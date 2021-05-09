<?php
//Page is used to collect students groups as well as information of the last messages sent to the chats to be displayed
//Get group_ids of groups the user is apart of
$sql = "SELECT group_id, unix_timestamp FROM groups WHERE group_id IN (SELECT group_id FROM groupmembers WHERE user_id = :user_id and status='active')";
$stmt = $conndb -> prepare($sql);

$stmt -> bindParam(":user_id", $details[1]);

$stmt -> execute();
$order_group_id = $stmt ->fetchAll(PDO::FETCH_COLUMN, 0);

$stmt -> execute();
$group_timestamp = $stmt ->fetchAll(PDO::FETCH_COLUMN, 1);

//Checks that group exists
if(count($order_group_id)!=0){
    //Order groups by time last message was sent or by data group was created
    $sql = "SELECT unix_timestamp FROM groupmessages WHERE group_id = :group_id";
    $stmt = $conndb -> prepare($sql);

    for($x=0;$x<count($order_group_id);$x++){

        $stmt -> bindParam(":group_id", $order_group_id[$x]);
        $stmt -> execute();
        
        if($stmt -> rowCount() != 0){
            $stmt -> execute();
            $timestamp[$stmt -> fetch()[0]] = $order_group_id[$x];
        }else{
            $timestamp[$group_timestamp[$x]] = $order_group_id[$x];
        }

    }

    //Sorts the array by timestamp
    krsort($timestamp);

    $group_id = array();

    foreach($timestamp as $value){
        array_push($group_id,$value);
    }

    //Runs for each group
    for($x=0;$x<count($group_id);$x++){
        
        //Get the group name and admin
        $sql = "SELECT name, admin_id FROM groups WHERE group_id = :group_id";
        $stmt = $conndb -> prepare($sql);

        if($stmt -> execute(["group_id"=>$group_id[$x]])){
            $info = $stmt -> fetch();
            $group_name[$x] = $info[0];
            $admin_id[$x] = $info[1];
        }else{
            $group_name[$x] = "<i>Couldn't receive name</i>";
            $admin_id[$x] = "";
        }

        //Get the last message sent to the group
        $sql = "SELECT sender_id, message, unix_timestamp FROM groupmessages WHERE group_id = :group_id ORDER BY message_id DESC";
        $stmt = $conndb -> prepare($sql);

        if($stmt -> execute(["group_id"=>$group_id[$x]])){
            $total_msg = $stmt -> rowCount();

            if($total_msg==0){

                $msg_available[$x] = 1;
                $displayedTime[$x] = "";
                $group_message[$x] = "<i>no messages</i>";

            }else if($stmt -> execute(["group_id"=>$group_id[$x]])){
                $msg = $stmt -> fetch();

                //If the message was sent by the user then add 'You: ' to the front of it
                $sender_id[$x] = $msg["sender_id"];
                $msg_available[$x] = 1;
                $group_message[$x] = $msg["message"];

                if($sender_id[$x]==$details[1]){
                    $group_message[$x] = "You: ".$msg["message"];
                }else{
                    $group_message[$x] = $msg["message"];
                }

                //timestamp since last message
                $tslmessage[$x] = time()-$msg["unix_timestamp"];

                if($tslmessage[$x]==1){
                    $displayedTime[$x] = $tslmessage[$x]. " second";
                }else if($tslmessage[$x]<60*2){
                    $displayedTime[$x] = $tslmessage[$x]. " seconds";
                }else if($tslmessage[$x]<(60*60*2)){
                    $displayedTime[$x] = floor($tslmessage[$x]/60). " minutes";
                }else if($tslmessage[$x]<(60*60*24*2)){
                    $displayedTime[$x] = floor($tslmessage[$x]/(60*60)). " hours";
                }else if($tslmessage[$x]<(60*60*24*7*2)){
                    $displayedTime[$x] = floor($tslmessage[$x]/(60*60*24)). " days";
                }else if($tslmessage[$x]<(60*60*24*52*2)){
                    $displayedTime[$x] = floor($tslmessage[$x]/(60*60*24*7)). " weeks";
                }else if($tslmessage[$x]>=(60*60*24*7*52)){
                    $displayedTime[$x] = "Over a year";
                }else{
                    $displayedTime[$x] = "";
                }
            }
        }else{
            $msg_available[$x] = 1;
            $displayedTime[$x] = "";
            $group_message[$x] = "<i>error</i>";
        }

        //Get the total numbrr of active users in the group
        $sql = "SELECT DISTINCT account_id FROM connections INNER JOIN session ON connections.session_id = session.session_id WHERE chat_id = :chat_id AND connections.type = 'group' and connections.status = 'active'";
        $stmt = $conndb -> prepare($sql);
        $stmt -> bindParam(":chat_id", $group_id[$x]);

        if($stmt -> execute()){
            $group_inchat[$x] = $stmt ->rowCount();
        }else{
            $group_inchat[$x] = 0;
        }
    }
}
?>