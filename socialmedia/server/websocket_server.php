
<?php
set_time_limit(0);

require '../php/conn.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
require_once '../vendor/autoload.php';

class Chat implements MessageComponentInterface {

    protected $clients;

	public function __construct() {
        $this->clients = new \SplObjectStorage;
	}

	public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

	}
    //Function runs when a client is closed
	public function onClose(ConnectionInterface $conn) {
        global $conndb;
        $a = array();
        $b = array();

        //Gets all the client Ids
        foreach($this->clients as $client){
            array_push($a,$client->resourceId);
        }

        //Deletes the disconnected client
        $this->clients->detach($conn);

        //Gets all the new client Ids
        foreach($this->clients as $client){
            array_push($b,$client->resourceId);
        }

        //array is made that includes the disconnceted client id by comparing both arrays made earlier
        $closedClientArray = array_diff($a,$b);

        //Client id is extracted from array and put in variable as a string
        $closeClientString = $closedClientArray[array_keys($closedClientArray)[0]];

        if(isset($closeClientString)){
            //Get account id using client
            $sql="SELECT account_id FROM session WHERE session_id =            
            (SELECT session_id FROM connections WHERE status = 'active' AND client_id =".$closeClientString.")";

            $stmt = $conndb ->prepare($sql);
            $stmt -> execute();
            $closed_user = $stmt->fetch()[0];

            //Checks if user has any clients open
            $sql = "SELECT connection_id FROM connections WHERE session_id IN
            (SELECT session_id FROM session WHERE account_id = :closed_user)
            AND status='active'";

            $stmt = $conndb ->prepare($sql);
            $stmt -> execute(["closed_user"=>$closed_user]);
            $totalconnections =$stmt -> rowCount();

            //if client has just one client open, there status is set to offline as that client has just closed
            if($totalconnections<=1){
                $online_status = "offline";

                //Get all the users the account was talking to
                //Then get those users active session Ids
                //Then get client Ids attached to those active sessions
                $sql = "SELECT client_id from connections WHERE session_id IN (
                    SELECT session_id FROM session WHERE account_id IN (
                        SELECT sender_id AS users FROM personalmessages WHERE receiver_id =  :closed_user
                        UNION
                        SELECT receiver_id FROM personalmessages WHERE sender_id =  :closed_user) AND status='active'
                ) AND status='active' AND type='personal'";

                $stmt = $conndb ->prepare($sql);
                $stmt -> execute(["closed_user"=>$closed_user]);
                $talkedto_clients = $stmt->fetchALL(PDO::FETCH_COLUMN, 0);
 
                //Notify active users that the user has disconnected
                foreach($this->clients as $client)
                {
                    $client_id = $client->resourceId;

                    for($x=0;$x<count($talkedto_clients);$x++){
                        if($client_id==$talkedto_clients[$x])
                        {
                            $client->send(json_encode(array("type"=>"joined_chat","status"=>"offline","user"=>$closed_user)));
                        }
                    }
                }

            }

            //Get chat ID of the client
            $sql = "SELECT chat_id, type FROM connections WHERE client_id = :client_id AND status = 'active'";
            $stmt = $conndb -> prepare($sql);
            $stmt ->execute(["client_id"=>$closeClientString]);
            $chat_id = $stmt -> fetch()[0] ?? null;
            $stmt ->execute(["client_id"=>$closeClientString]);
            $grouptype =  $stmt -> fetch()[1];

            //Client is set to inactive in database
            $sql = "UPDATE connections SET status = 'inactive' WHERE client_id = :client";
            $stmt = $conndb->prepare($sql);
            $stmt -> bindParam(":client", $closeClientString);
            $stmt->execute();

            if($chat_id!=null){

                //Get total active clients of user in chat
                $sql = 'SELECT connection_id FROM connections INNER JOIN session ON connections.session_id = session.session_id 
                WHERE chat_id = :chat_id AND account_id = :account_id AND connections.status = "active"';

                $stmt = $conndb -> prepare($sql);

                $stmt ->execute(["chat_id"=>$chat_id,"account_id"=>$closed_user]);
                $total_clients_in_chat =  $stmt -> rowCount();

                if($total_clients_in_chat==0){

                    //get total users in chat left
                    $sql = "SELECT DISTINCT account_id FROM connections INNER JOIN session ON connections.session_id = session.session_id WHERE chat_id= :chat_id AND connections.status = 'active'";
                    $stmt = $conndb -> prepare($sql);
                    $stmt -> bindParam(":chat_id", $chat_id);
                    $stmt -> execute();
                    $total_in_left_chat = $stmt -> rowCount();

                    //notify client of increase in chat members
                    $sql = "SELECT client_id FROM connections WHERE type = :type AND chat_id = :chat_id AND status = 'active'";
                    $stmt = $conndb ->prepare($sql);
                    $stmt -> execute(["chat_id"=>$chat_id,"type"=>$grouptype]);
                    $talkedto_clients = $stmt->fetchALL(PDO::FETCH_COLUMN, 0);

                    foreach($this->clients as $client)
                    {
                        $client_id = $client->resourceId;

                        for($x=0;$x<count($talkedto_clients);$x++){
                            if($client_id==$talkedto_clients[$x])
                            {
                                if($grouptype=="group"){
                                    $client->send(json_encode(array("type"=>"user_online_status","user_id"=>$closed_user,"left"=>$chat_id,"left_total"=>$total_in_left_chat)));
                                }else if($grouptype=="course"){
                                    $client->send(json_encode(array("type"=>"joined_chat","left"=>$chat_id,"left_total"=>$total_in_left_chat)));
                                }
                            }
                        }
                    }
                }
            }
        }
	}

    //Function runs when the server retrieves a message from a client
	public function onMessage(ConnectionInterface $from,  $data) {
        
        global $conndb;
        //Senders client id
        $from_id = $from->resourceId;
        $data = json_decode($data);
        
        $sender_id = $data->sender_id;
        $session_code = $data->session_code;
        $chat = $data->chat;
        
        //Get session id of sender
        $sql = "SELECT session_id FROM session WHERE generatedCode = :session_code AND account_id = :sender_id AND status = 'active'";
        $stmt = $conndb->prepare($sql);
        $stmt->execute([":sender_id"=>$sender_id,":session_code"=>$session_code]);
        $rowcount = $stmt -> rowCount();

        $stmt->execute([":sender_id"=>$sender_id,":session_code"=>$session_code]);
        $session_id = $stmt ->fetch()["session_id"];

        //Makes sure the user is authentic
        if($rowcount!=0){

		$type = $data->type;
		switch ($type) {
            //If the message is sent to a course or module then this runs
            case 'cm':

                $sender_id = $data->sender_id;
                $receiver_id = $data->receiver_id;
                $chat_msg = htmlentities($data->chat_msg);
                $unix_timestamp = time();
                if(strlen($chat_msg)!=0){
                    
                    if(strlen($receiver_id)==4){
                        //Get course session ids
                        $sql = "SELECT session_id FROM session WHERE account_id IN (SELECT student_id FROM studentcourse WHERE course_id = :receiver_id AND student_id != :sender_id) AND status= 'active'";
                        $stmt = $conndb->prepare($sql);
                        $stmt -> execute([":receiver_id"=>$receiver_id,":sender_id"=>$sender_id]);
                        if($stmt -> rowCount()>0){
                            $stmt -> execute([":receiver_id"=>$receiver_id,":sender_id"=>$sender_id]);
                            $receiver_session_ids = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);
                            $receiver_session_ids_imploded = implode(",", $receiver_session_ids);

                            //Get receiver client ids from all their active
                            $sql = "SELECT client_id FROM connections WHERE session_id IN($receiver_session_ids_imploded) AND status= 'active' AND type='course'";
                            $stmt = $conndb->prepare($sql);
                            $stmt -> execute();
                            $receiver_client_ids = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);
                        }else{
                            $receiver_client_ids = [];
                        }

                    }else if(strlen($receiver_id)==8){
                         //Get module session ids
                         $sql = "SELECT session_id FROM session WHERE account_id IN (SELECT student_id FROM studentmodule WHERE module_id = :receiver_id AND student_id != :sender_id) AND status= 'active'";
                         $stmt = $conndb->prepare($sql);
                         $stmt -> execute([":receiver_id"=>$receiver_id,":sender_id"=>$sender_id]);
                        if($stmt -> rowCount()>0){
                            $stmt -> execute([":receiver_id"=>$receiver_id,":sender_id"=>$sender_id]);
                            $receiver_session_ids = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);
                            $receiver_session_ids_imploded = implode(",", $receiver_session_ids);

                            //Get receiver client ids from all their active
                            $sql = "SELECT client_id FROM connections WHERE session_id IN($receiver_session_ids_imploded) AND status= 'active' AND type='course'";
                            $stmt = $conndb->prepare($sql);
                            $stmt -> execute();
                            $receiver_client_ids = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);
                        }else{
                            $receiver_client_ids = [];
                        }
                    }

                        //Insert message
                        $sql = "INSERT INTO cmmessages (sender_id, group_id, message, unix_timestamp)
                        VALUES (:sender_id, :group_id, :chat_msg, :unix_timestamp)";
                        $stmt = $conndb->prepare($sql);
                        $stmt->execute(['sender_id'=>$sender_id, 'group_id'=>$receiver_id, 'chat_msg'=>$chat_msg, 'unix_timestamp'=>$unix_timestamp]);

                        //Fetch message id
                        $sql = "SELECT message_id FROM cmmessages ORDER BY message_id DESC LIMIT 1";
                        $stmt = $conndb->prepare($sql);
                        $stmt->execute();
                        $message_id = $stmt -> fetch();

                    if(count($receiver_client_ids)!=0){
                    
                        //Get sender name
                        $sql = "SELECT fname, lname FROM accounts WHERE user_id = :sender_id";
                        $stmt = $conndb->prepare($sql);
                        $stmt -> bindParam(":sender_id", $sender_id);
                        $stmt->execute();
                        $sender = $stmt -> fetch();           

                        //Send message
                        foreach($this->clients as $client)
                        {
                            $client_id = $client->resourceId;

                            for($x=0;$x<count($receiver_client_ids);$x++){
                                if($client_id==$receiver_client_ids[$x])
                                {
                                    $client->send(json_encode(array("cm"=>$receiver_id, "message_id"=>$message_id["message_id"], "timestamp"=>$unix_timestamp,"type"=>$type,"msg"=>$chat_msg,"sender_id"=>$sender_id,"fname"=>ucfirst($sender["fname"]),"lname"=>ucfirst($sender["lname"]))));
                                }
                            }
                        }
                    }

                    //Get senders session ids
                    $sql = "SELECT session_id FROM session WHERE account_id = :sender_id && status= 'active'";
                    $stmt = $conndb->prepare($sql);
                    $stmt -> bindParam(":sender_id", $sender_id);
                    $stmt->execute();
                    $sender_session_ids = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);
                    $sender_session_ids_imploded = implode(",", $sender_session_ids);

                    //Get sender client ids from all their active
                    $sql = "SELECT client_id FROM connections WHERE session_id IN($sender_session_ids_imploded) && status= 'active' AND type='course'";
                    $stmt = $conndb->prepare($sql);
                    $stmt->execute(["client_id"=>$from_id]);
                    $sender_client_ids = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);

                    //send success message to all 
                    if(count($sender_client_ids)!=0){

                        foreach($this->clients as $client)
                        {
                            $client_id = $client->resourceId;

                            for($x=0;$x<count($sender_client_ids);$x++){
                                if($client_id==$sender_client_ids[$x] && $client_id!=$from_id){
                                    $client->send(json_encode(array("from"=>"other","cm"=>$receiver_id,"msg"=>$chat_msg,"type"=>"confirm")));
                                }else if($client_id==$from_id){
                                    $client->send(json_encode(array("from"=>"current","cm"=>$receiver_id,"msg"=>$chat_msg,"type"=>"confirm")));
                                }
                            }
                        }

                    }
                }else{
                    $from->send(json_encode(array("msg"=>"empty_message","type"=>"error")));
                }
                break;
                //If the message is sent to a group then this runs
                case 'gm':

                    $sender_id = $data->sender_id;
                    $receiver_id = $data->receiver_id;
                    $chat_msg = htmlentities($data->chat_msg);
                    $unix_timestamp = time();

                    //Makes sure user is part of the group
                    $sql = "SELECT user_id FROM groupmembers WHERE group_id = :receiver_id AND user_id = :sender_id AND status= 'active'";
                    $stmt = $conndb->prepare($sql);
                    $stmt -> execute([":receiver_id"=>$receiver_id,":sender_id"=>$sender_id]);

                    if(strlen($chat_msg)!=0 && $stmt -> rowCount() !=0){

                            //Get active group members session ids
                            $sql = "SELECT session_id FROM session WHERE account_id IN (SELECT user_id FROM groupmembers WHERE group_id = :receiver_id AND user_id != :sender_id AND status= 'active') AND status= 'active'";
                            $stmt = $conndb->prepare($sql);
                            $stmt -> execute([":receiver_id"=>$receiver_id,":sender_id"=>$sender_id]);

                            if($stmt -> rowCount()>0){
                                $stmt -> execute([":receiver_id"=>$receiver_id,":sender_id"=>$sender_id]);
                                $receiver_session_ids = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);
                                $receiver_session_ids_imploded = implode(",", $receiver_session_ids);                            

                                //Get receiver client ids from all their active
                                $sql = "SELECT client_id FROM connections WHERE session_id IN($receiver_session_ids_imploded) AND status= 'active' AND type='group'";
                                $stmt = $conndb->prepare($sql);
                                $stmt -> execute();
                                $receiver_client_ids = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);
                            }else{
                                $receiver_client_ids = [];
                            }

                            //Insert message
                            $sql = "INSERT INTO groupmessages (sender_id, group_id, message, unix_timestamp)
                            VALUES (:sender_id, :group_id, :chat_msg, :unix_timestamp)";
                            $stmt = $conndb->prepare($sql);
                            $stmt->execute(['sender_id'=>$sender_id, 'group_id'=>$receiver_id, 'chat_msg'=>$chat_msg, 'unix_timestamp'=>$unix_timestamp]);

                            //Fetch message id
                            $sql = "SELECT message_id FROM groupmessages ORDER BY message_id DESC LIMIT 1";
                            $stmt = $conndb->prepare($sql);
                            $stmt->execute();
                            $message_id = $stmt -> fetch();
    
                        if(count($receiver_client_ids)!=0){
                        
                            //Get sender name
                            $sql = "SELECT fname, lname FROM accounts WHERE user_id = :sender_id";
                            $stmt = $conndb->prepare($sql);
                            $stmt -> bindParam(":sender_id", $sender_id);
                            $stmt->execute();
                            $sender = $stmt -> fetch();           
    
                            //Send message
                            foreach($this->clients as $client)
                            {
                                $client_id = $client->resourceId;
    
                                for($x=0;$x<count($receiver_client_ids);$x++){
                                    if($client_id==$receiver_client_ids[$x])
                                    {
                                        $client->send(json_encode(array("group"=>$receiver_id, "message_id"=>$message_id["message_id"], "timestamp"=>$unix_timestamp,"type"=>$type,"msg"=>$chat_msg,"sender_id"=>$sender_id,"fname"=>ucfirst($sender["fname"]),"lname"=>ucfirst($sender["lname"]))));
                                    }
                                }
                            }
                        }
    
                        //Get senders session ids
                        $sql = "SELECT session_id FROM session WHERE account_id = :sender_id && status= 'active'";
                        $stmt = $conndb->prepare($sql);
                        $stmt -> bindParam(":sender_id", $sender_id);
                        $stmt->execute();
                        $sender_session_ids = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);
                        $sender_session_ids_imploded = implode(",", $sender_session_ids);
    
                        //Get sender client ids from all their active
                        $sql = "SELECT client_id FROM connections WHERE session_id IN($sender_session_ids_imploded) && status= 'active' AND type='group'";
                        $stmt = $conndb->prepare($sql);
                        $stmt->execute();
                        $sender_client_ids = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);
    
                        //send success message to all 
                        if(count($sender_client_ids)!=0){
    
                            foreach($this->clients as $client)
                            {
                                $client_id = $client->resourceId;
    
                                for($x=0;$x<count($sender_client_ids);$x++){
                                    if($client_id==$sender_client_ids[$x] && $client_id!=$from_id){
                                        $client->send(json_encode(array("from"=>"other","group"=>$receiver_id,"msg"=>$chat_msg,"type"=>"confirm")));
                                    }else if($client_id==$from_id){
                                        $client->send(json_encode(array("from"=>"current","group"=>$receiver_id,"msg"=>$chat_msg,"type"=>"confirm")));
                                    }
                                }
                            }
    
                        }
                    }else{
                        $from->send(json_encode(array("msg"=>"empty_message","type"=>"error")));
                    }
                    break;
            //If the message is sent to a person then this runs
            case "pm":
                
                $sender_id = $data->sender_id;
                $receiver_id = $data->receiver_id;
                $chat_msg = htmlentities($data->chat_msg);
                $unix_timestamp = time();

                if(strlen($chat_msg)!=0){

                    //Get receivers session ids
                    $sql = "SELECT session_id FROM session WHERE account_id = :receiver_id AND status= 'active'";
                    $stmt = $conndb->prepare($sql);
                    $stmt -> bindParam(":receiver_id", $receiver_id);
                    $stmt -> execute();
                    if($stmt -> rowCount()>0){
                        $stmt -> execute();
                        $receiver_session_ids = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);
                        $receiver_session_ids_imploded = implode(",", $receiver_session_ids);  

                        //Get receiver client ids from all their active
                        $sql = "SELECT client_id FROM connections WHERE session_id IN($receiver_session_ids_imploded) AND status= 'active' AND type='personal'";
                        $stmt = $conndb->prepare($sql);
                        $stmt -> execute();
                        $receiver_client_ids = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);
                    }else{
                        $receiver_client_ids = [];
                    }

                    //Insert pm
                    $sql = "INSERT INTO personalmessages (sender_id, receiver_id, message, unix_timestamp, seen)
                    VALUES (:sender_id, :receiver_id, :chat_msg, :unix_timestamp, 'no')";
                    $stmt = $conndb->prepare($sql);
                    $stmt->execute(['sender_id'=>$sender_id, 'receiver_id'=>$receiver_id, 'chat_msg'=>$chat_msg, 'unix_timestamp'=>$unix_timestamp]);

                    //Fetch message id
                    $sql = "SELECT message_id FROM personalmessages ORDER BY message_id DESC LIMIT 1";
                    $stmt = $conndb->prepare($sql);
                    $stmt->execute();
                    $message_id = $stmt -> fetch();    

                    if(count($receiver_client_ids)!=0){
                    
                        //Get sender name
                        $sql = "SELECT fname, lname FROM accounts WHERE user_id = :sender_id";
                        $stmt = $conndb->prepare($sql);
                        $stmt -> bindParam(":sender_id", $sender_id);
                        $stmt->execute();
                        $sender = $stmt -> fetch();           

                        //Send message
                        foreach($this->clients as $client)
                        {
                            $client_id = $client->resourceId;

                            for($x=0;$x<count($receiver_client_ids);$x++){
                                if($client_id==$receiver_client_ids[$x])
                                {
                                    $client->send(json_encode(array("message_id"=>$message_id["message_id"], "timestamp"=>$unix_timestamp,"type"=>$type,"msg"=>$chat_msg,"sender_id"=>$sender_id,"fname"=>ucfirst($sender["fname"]),"lname"=>ucfirst($sender["lname"]))));
                                }
                            }
                        }
                    }
                    //Get senders session ids
                    $sql = "SELECT session_id FROM session WHERE account_id = :sender_id && status= 'active'";
                    $stmt = $conndb->prepare($sql);
                    $stmt -> bindParam(":sender_id", $sender_id);
                    $stmt->execute();
                    $sender_session_ids = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);
                    $sender_session_ids_imploded = implode(",", $sender_session_ids);

                    //Get sender client ids from all their active
                    $sql = "SELECT client_id FROM connections WHERE session_id IN($sender_session_ids_imploded) && status= 'active' AND type='personal'";
                    $stmt = $conndb->prepare($sql);
                    $stmt->execute();
                    $sender_client_ids = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);

                    //send success message to all 
                    if(count($sender_client_ids)!=0){

                        foreach($this->clients as $client)
                        {
                            $client_id = $client->resourceId;

                            for($x=0;$x<count($sender_client_ids);$x++){
                                if($client_id==$sender_client_ids[$x] && $client_id!=$from_id){
                                    $client->send(json_encode(array("from"=>"other","receiver_id"=>$receiver_id,"msg"=>$chat_msg,"type"=>"confirm")));
                                }else if($client_id==$from_id){
                                    $client->send(json_encode(array("from"=>"current","receiver_id"=>$receiver_id,"msg"=>$chat_msg,"type"=>"confirm")));
                                }
                            }
                        }

                    }
                }else{
                    $from->send(json_encode(array("msg"=>"empty_message","type"=>"error")));
                }
                break;
            //When a client connects then this runs    
            case "log":


                //If the client id is used by another user then it is deactivated
                $sql = "UPDATE connections SET status = 'inactive' WHERE client_id = :from_id";
                $stmt = $conndb->prepare($sql);
                $stmt -> bindParam(":from_id", $from_id);
                $stmt->execute();
                
                //Link client to session in database
                $sql="INSERT INTO connections(session_id,client_id,type,status) VALUES (:session_id,:from_id,:type,'active')";
                $stmt = $conndb->prepare($sql);
                $stmt->execute([":from_id"=> $from_id, ":type"=>$chat, ":session_id"=> $session_id]);

                //Get all the users the account was talking to
                //Then get those users active session Ids
                //Then get client Ids attached to those active sessions
                $sql = "SELECT client_id from connections WHERE session_id IN (
                    SELECT session_id FROM session WHERE account_id IN (
                        SELECT sender_id AS users FROM personalmessages WHERE receiver_id =  $sender_id
                        UNION
                        SELECT receiver_id FROM personalmessages WHERE sender_id =  $sender_id) AND status='active'
                ) AND status='active' AND type='personal'";

                $stmt = $conndb ->prepare($sql);
                $stmt -> execute();
                $talkedto_clients = $stmt->fetchALL(PDO::FETCH_COLUMN, 0);

                //Notify active users that the user has connected
                foreach($this->clients as $client)
                {
                    $client_id = $client->resourceId;

                    for($x=0;$x<count($talkedto_clients);$x++){
                        if($client_id==$talkedto_clients[$x])
                        {
                            $client->send(json_encode(array("type"=>"user_online_status","status"=>"online","user"=>$sender_id)));
                        }
                    }
                }

                break;
            //When a client joins a group, course or module this runs    
            case "joinedchat":

                $chat_id = $data->chat_id;

                //Chat Id of the joined group/course is collected
                $sql= "SELECT chat_id FROM connections WHERE client_id = :client_id AND status = 'active'";
                $stmt = $conndb -> prepare($sql);

                if($stmt -> execute(["client_id"=>$from_id])){
                    $chat_id_left = $stmt->fetch()[0];
                }else{
                    $chat_id_left = 'none';
                }
                
                //If the user didn't join a different chat then the case is no longer run
                if($chat_id!=$chat_id_left){

                    $sql= "UPDATE connections SET chat_id = :chat_id WHERE client_id = :client_id AND status = 'active'";
                    $stmt = $conndb -> prepare($sql);
                    
                    if($stmt -> execute(["chat_id"=>$chat_id,"client_id"=>$from_id])){

                        //get total users in chat joined
                        $sql = "SELECT DISTINCT account_id FROM connections INNER JOIN session ON connections.session_id = session.session_id WHERE chat_id= :chat_id AND connections.status = 'active'";
                        $stmt = $conndb -> prepare($sql);
                        $stmt -> bindParam(":chat_id", $chat_id);
                        $stmt -> execute();
                        $total_in_joined_chat = $stmt -> rowCount();

                        //get total users in chat left
                        $sql = "SELECT DISTINCT account_id FROM connections INNER JOIN session ON connections.session_id = session.session_id WHERE chat_id= :chat_id AND connections.status = 'active'";
                        $stmt = $conndb -> prepare($sql);
                        $stmt -> bindParam(":chat_id", $chat_id_left);
                        $stmt -> execute();
                        $total_in_left_chat = $stmt -> rowCount();

                        //notify client of increase in chat members
                        $sql = "SELECT client_id FROM connections WHERE type = :type AND status = 'active'";
                        $stmt = $conndb ->prepare($sql);
                        $stmt -> execute(["type"=>$chat]);
                        $talkedto_clients = $stmt->fetchALL(PDO::FETCH_COLUMN, 0);

                        foreach($this->clients as $client)
                        {
                            $client_id = $client->resourceId;

                            for($x=0;$x<count($talkedto_clients);$x++){
                                if($client_id==$talkedto_clients[$x])
                                {   
                                    if($chat=="course"){
                                        $client->send(json_encode(array("type"=>"joined_chat","left"=>$chat_id_left,"joined"=>$chat_id,"left_total"=>$total_in_left_chat,"joined_total"=>$total_in_joined_chat)));
                                    }else if($chat=="group"){
                                        $client->send(json_encode(array("type"=>"user_online_status","user_id"=>$sender_id, "left"=>$chat_id_left,"joined"=>$chat_id,"left_total"=>$total_in_left_chat,"joined_total"=>$total_in_joined_chat)));
                                    }
                                }
                            }
                        }
                        

                    };
                }

                break;

                //When a user is added to a group this runs
                case "added_to_group":

                    $chat_id = $data->chat_id;
                    $user_id = $data->user_id;
                    //Notifies clients the user has activate that they were added to a group
                    $sql = "SELECT client_id FROM connections WHERE session_id IN(
                        SELECT session_id FROM session WHERE account_id = :user_id and status='active'
                        ) and status = 'active' AND type='group'";

                    $stmt = $conndb -> prepare($sql);
                    $stmt -> bindParam(":user_id", $user_id);
                    $stmt -> execute();
                    
                    
                    if($stmt -> rowCount() > 0){
                        $stmt -> execute();
                        $talkedto_clients = $stmt ->fetchALL(PDO::FETCH_COLUMN, 0);

                        foreach($this->clients as $client)
                        {
                            $client_id = $client->resourceId;

                            for($x=0;$x<count($talkedto_clients);$x++){
                                if($client_id==$talkedto_clients[$x])
                                {   
                                    $client->send(json_encode(array("type"=>"added_to_group","group"=>$chat_id)));
                                }
                            }
                        }
                    }

                    //Tells clients of that group that they would refresh the user list to show the new user list
                    $sql = "SELECT client_id FROM connections WHERE status = 'active' AND type='group' AND chat_id= :chat_id";

                    $stmt = $conndb -> prepare($sql);
                    $stmt -> bindParam(":chat_id", $chat_id);
                    $stmt -> execute();
  
                    if($stmt -> rowCount() > 0){
                        $stmt -> execute();
                        $talkedto_clients = $stmt ->fetchALL(PDO::FETCH_COLUMN, 0);

                        foreach($this->clients as $client)
                        {
                            $client_id = $client->resourceId;

                            for($x=0;$x<count($talkedto_clients);$x++){
                                if($client_id==$talkedto_clients[$x])
                                {   
                                    $client->send(json_encode(array("type"=>"refresh_users","group"=>$chat_id)));
                                }
                            }
                        }
                    }

                break;

                case "left_group":

                    $chat_id = $data->chat_id;
                    //Notifies clients the user has activate that they left the group
                    $sql = "SELECT client_id FROM connections WHERE session_id IN(
                        SELECT session_id FROM session WHERE account_id = :user_id and status='active'
                        ) and status = 'active' AND type='group'";
                    $stmt = $conndb -> prepare($sql);
                    $stmt -> bindParam(":user_id", $sender_id);

                    $stmt -> execute();

                    if($stmt -> rowCount() > 0){
                        $stmt -> execute();
                        $talkedto_clients = $stmt ->fetchALL(PDO::FETCH_COLUMN, 0);

                        foreach($this->clients as $client)
                        {
                            $client_id = $client->resourceId;

                            for($x=0;$x<count($talkedto_clients);$x++){
                                if($client_id==$talkedto_clients[$x])
                                {   
                                    $client->send(json_encode(array("type"=>"left_group","group"=>$chat_id)));
                                }
                            }
                        }
                    }

                    //Tells clients of that group that they would refresh the user list to show the new user list
                    $sql = "SELECT client_id FROM connections WHERE status = 'active' AND chat_id= :chat_id AND type='group'";
                    $stmt = $conndb -> prepare($sql);
                    $stmt -> bindParam(":chat_id", $chat_id);
                    $stmt -> execute();
                    if($stmt -> rowCount() > 0){
                        $stmt -> execute();
                        $talkedto_clients = $stmt ->fetchALL(PDO::FETCH_COLUMN, 0);

                        foreach($this->clients as $client)
                        {
                            $client_id = $client->resourceId;

                            for($x=0;$x<count($talkedto_clients);$x++){
                                if($client_id==$talkedto_clients[$x])
                                {   
                                    $client->send(json_encode(array("type"=>"refresh_users","group"=>$chat_id)));
                                }
                            }
                        }
                    }

                break;

                case "deleted_group":

                    $chat_id = $data->chat_id;

                    $sql = "SELECT * FROM groups WHERE group_id = :group_id AND status = 'inactive'";
                    $stmt = $conndb -> prepare($sql);
                    $stmt -> execute([":group_id"=>$chat_id]);
                    //If the group is deleted then notify all clients in that group to remove it as a option from teh group list
                    if($stmt -> rowCount() > 0){

                        $sql = "SELECT client_id FROM connections WHERE session_id IN(
                            SELECT session_id FROM session WHERE account_id IN (
                                SELECT DISTINCT user_id FROM groupmembers WHERE group_id = :group_id
                                ) and status='active'
                            ) and status = 'active' AND type='group'";
                        $stmt = $conndb -> prepare($sql);
                        $stmt -> bindParam(":group_id", $chat_id);
        
                        $stmt -> execute();
      
                        if($stmt -> rowCount() > 0){
                            $stmt -> execute();
                            $talkedto_clients = $stmt ->fetchALL(PDO::FETCH_COLUMN, 0);

                            foreach($this->clients as $client)
                            {
                                $client_id = $client->resourceId;

                                for($x=0;$x<count($talkedto_clients);$x++){
                                    if($client_id==$talkedto_clients[$x])
                                    {   
                                        $client->send(json_encode(array("type"=>"left_group","group"=>$chat_id)));
                                    }
                                }
                            }
                        }
                    }
                    break;

                    case "created_group":

                        $chat_id = $data->chat_id;

                        //Check that group exists
                        $sql = "SELECT * FROM groups WHERE group_id = :group_id AND status = 'active'";
                        $stmt = $conndb -> prepare($sql);
                        $stmt -> execute([":group_id"=>$chat_id]);

                        if($stmt -> rowCount() > 0){
                            //Tells clients of the user that they created a new group
                            $sql = "SELECT client_id FROM connections WHERE session_id IN(
                                SELECT session_id FROM session WHERE account_id = :user_id and status='active'
                                ) and status = 'active' AND type='group'";
                            $stmt = $conndb -> prepare($sql);
                            $stmt -> bindParam(":user_id", $sender_id);
      
                            $stmt -> execute();
       
                            if($stmt -> rowCount() > 0){
                                $stmt -> execute();
                                $talkedto_clients = $stmt ->fetchALL(PDO::FETCH_COLUMN, 0);

                                foreach($this->clients as $client)
                                {
                                    $client_id = $client->resourceId;
    
                                    for($x=0;$x<count($talkedto_clients);$x++){
                                        if($client_id==$talkedto_clients[$x])
                                        {   
                                            $client->send(json_encode(array("type"=>"added_to_group","group"=>$chat_id)));
                                        }
                                    }
                                }
                            }
                        }
                        break;

                case "removed_user":

                    $chat_id = $data->chat_id;
                    $removed_id = $data->removed_id;

                    //Make sure user is not in the group before group display is removed
                    $sql = "SELECT * FROM groupmembers WHERE group_id = :group_id AND user_id = :user_id AND status = 'inactive'";
                    $stmt = $conndb -> prepare($sql);
                    $stmt -> execute([":user_id"=>$removed_id, ":group_id"=>$chat_id]);

                    if($stmt -> rowCount() > 0){
                        //Tells the user's clients that they have be removed from the group
                        $sql = "SELECT client_id FROM connections WHERE session_id IN(
                            SELECT session_id FROM session WHERE account_id = :user_id and status='active'
                            ) and status = 'active' AND type='group'";

                        $stmt = $conndb -> prepare($sql);
                        $stmt -> bindParam(":user_id", $removed_id);

                        $stmt -> execute();

                        if($stmt -> rowCount() > 0){
                            $stmt -> execute();
                            $talkedto_clients = $stmt ->fetchALL(PDO::FETCH_COLUMN, 0);

                            foreach($this->clients as $client)
                            {
                                $client_id = $client->resourceId;

                                for($x=0;$x<count($talkedto_clients);$x++){
                                    if($client_id==$talkedto_clients[$x])
                                    {   
                                        $client->send(json_encode(array("type"=>"left_group","group"=>$chat_id)));
                                    }
                                }
                            }
                        }

                        //Tells clients of that group that they would refresh the user list to show the new user list
                        $sql = "SELECT client_id FROM connections WHERE status = 'active' AND chat_id= :chat_id AND type='group'";
                        $stmt = $conndb -> prepare($sql);
                        $stmt -> bindParam(":chat_id", $chat_id);
                        $stmt -> execute();
                        if($stmt -> rowCount() > 0){
                            $stmt -> execute();
                            $talkedto_clients = $stmt ->fetchALL(PDO::FETCH_COLUMN, 0);

                            foreach($this->clients as $client)
                            {
                                $client_id = $client->resourceId;

                                for($x=0;$x<count($talkedto_clients);$x++){
                                    if($client_id==$talkedto_clients[$x])
                                    {   
                                        $client->send(json_encode(array("type"=>"refresh_users","group"=>$chat_id)));
                                    }
                                }
                            }
                        }
                    }

                break;

                case "new_admin":

                    $chat_id = $data->chat_id;
                    $admin_id = $data->admin_id;

                    //Make sure user is the admin before it is sent
                    $sql = "SELECT * FROM groups WHERE admin_id = :admin_id AND status = 'active'";
                    $stmt = $conndb -> prepare($sql);
                    $stmt -> execute([":admin_id"=>$admin_id]);

                    if($stmt -> rowCount() > 0){
                        //Tells clients of that group that they would refresh the user list to show the new user list
                        $sql = "SELECT client_id FROM connections WHERE status = 'active' AND chat_id= :chat_id AND type='group'";
                        $stmt = $conndb -> prepare($sql);
                        $stmt -> bindParam(":chat_id", $chat_id);
                        $stmt -> execute();
                        if($stmt -> rowCount() > 0){
                            $stmt -> execute();
                            $talkedto_clients = $stmt ->fetchALL(PDO::FETCH_COLUMN, 0);

                            foreach($this->clients as $client)
                            {
                                $client_id = $client->resourceId;

                                for($x=0;$x<count($talkedto_clients);$x++){
                                    if($client_id==$talkedto_clients[$x])
                                    {   
                                        $client->send(json_encode(array("type"=>"new_admin","group"=>$chat_id, "admin_id"=>$admin_id)));
                                    }
                                }
                            }
                        }
                    }

                break;
            }    				
		}else{
            //Sends error message if user session is wrong
            $from->send(json_encode(array("msg"=>"invalid_user","type"=>"error")));
        }
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		$conn->close();
	}
}
$server = IoServer::factory(
	new HttpServer(new WsServer(new Chat())),
	8080
);
$server->run();
?>