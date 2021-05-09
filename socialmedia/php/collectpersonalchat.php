<?php
//Page is used to collect students personal chats as well as information of the last messages sent to the chats to be displayed
//Get all users talked to
$sql = "SELECT sender_id AS users FROM personalmessages WHERE receiver_id = $details[1]
UNION
SELECT receiver_id FROM personalmessages WHERE sender_id = $details[1]";

$stmt = $conndb ->prepare($sql);

if($stmt -> execute()){
    $talkedto = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);

    if(isset($_GET["pm"])||count($talkedto)!=0){

    //Prevents code form running if user hasnt previously talked to anoyone
    if(count($talkedto)!=0){
        //sort user ids by last message sent or received with user

        for($x=0;$x<sizeof($talkedto);$x++){
            //Get Id of last message sent from either person then the use id of the sender
            $sql = "SELECT message_id, sender_id FROM personalmessages WHERE message_id = (SELECT max(message_id) FROM personalmessages  WHERE (receiver_id = ". $talkedto[$x]. " AND sender_id = $details[1]) OR (receiver_id = $details[1] AND sender_id = ". $talkedto[$x]. "))";
            $stmt = $conndb -> prepare($sql);
            
            if($stmt->execute()){
                $msg_info =$stmt -> fetch();

                $message_id[$x] = $msg_info["message_id"];
                $sender_id[$x] = $msg_info["sender_id"];

                //Check whether user received or sent it
                if($sender_id[$x]!=$details[1]){
                    $direction[$x]="received";
                }else{
                    $direction[$x]="sent";
                }
    
                //match make the users key the id of last message sent or received
                $gathered_info_users[$message_id[$x]] = $talkedto[$x];
                $gathered_info_direction[$message_id[$x]] = $direction[$x];

            }
        }
    

        //sort the array
        krsort($gathered_info_users);
        krsort($gathered_info_direction);
    }
        $talkedto_ordered = array();
        $direction_ordered = array();

    //Prevents code form running if user hasnt previously talked to anoyone
    if(count($talkedto)!=0){
        //new array to have keys increasing by 1 from 0
        foreach($gathered_info_users as $key => $value){
            array_push($talkedto_ordered,$gathered_info_users[$key]);
        }

        foreach($gathered_info_direction as $key => $value){
            array_push($direction_ordered,$gathered_info_direction[$key]);
        }
    }
        
        //Checks to see if person user wants to message has been messaged before
        if(isset($_GET["pm"])){
            $pm_id = $_GET["pm"];

            if(in_array($pm_id,$talkedto)){
                $already_messaged = "true";
            }else{
                $already_messaged = "false";
                array_unshift($talkedto_ordered,$pm_id);
                array_unshift($direction_ordered,"neither");
            }
        }else{
            $already_messaged = "invalid";
        }

        //Get information of each user
        for($x=0;$x<sizeof($talkedto_ordered);$x++){

            //User information
            $sql = "SELECT fname, lname, cyear, picture FROM accounts WHERE user_id = ".$talkedto_ordered[$x];
            $stmt = $conndb -> prepare($sql);

            if($stmt ->execute()){
                $pm_user = $stmt -> fetch();

                $pm_fname[$x] = $pm_user['fname'];
                $pm_lname[$x] = $pm_user['lname'];
                $pm_cyear[$x] = $pm_user['cyear'];
                $pm_picture[$x] = $pm_user['picture'];
            
                //formal year
                if($pm_cyear[$x]==1){
                    $pm_indicator[$x]="st year";
                }else if($pm_cyear[$x]==2){
                    $pm_indicator[$x]="nd year";
                }else if($pm_cyear[$x]==3){
                    $pm_indicator[$x]="rd year";
                }else if ($pm_cyear[$x]==null && $pm_cyear[$x]== 0){
                    $pm_indicator[$x]="";
                }else{
                    $pm_indicator[$x]="th year";
                }

                //course
                $sql = "SELECT cname FROM studentcourse INNER JOIN courses ON studentcourse.course_id = courses.course_id WHERE student_id=". $talkedto_ordered[$x];
                $stmt = $conndb -> prepare($sql);
                $stmt ->execute();
                

                if($stmt -> rowCount()>0){
                    $stmt ->execute();
                    $pm_course[$x] = $stmt -> fetch()["cname"];

                }else{
                    $pm_course[$x] = "";
                }
                
                //get users online status
                $sql = "SELECT connection_id FROM connections WHERE session_id in(
                    SELECT session_id FROM session WHERE status = 'active' AND account_id = ".$talkedto_ordered[$x].")
                    AND status='active'";
                $stmt = $conndb -> prepare($sql);
                $stmt -> execute();
                $totalActiveClients = $stmt-> rowCount();

                if($totalActiveClients==0){
                    $onlineStatus[$x] = "offline";
                }else{
                    $onlineStatus[$x] = "online";
                }

                //Get total unread messages
                $sql = "SELECT count(message_id) FROM personalmessages WHERE sender_id= :sender_id AND receiver_id = :receiver_id AND seen='no'";
                $stmt = $conndb -> prepare($sql);
                $stmt -> execute(["sender_id"=>$talkedto_ordered[$x],"receiver_id"=>$details[1]]);
                $totalunseen[$x] = $stmt-> fetch()[0];

                if($already_messaged=="false"&&$x==0){
                    $pm_message[$x] = "<i>No messages</i>";
                    $msg_available[$x] = 0;
                    $displayedTime[$x] = "";
                }else{
                    //last message
                    $sql = "SELECT message, unix_timestamp FROM personalmessages WHERE message_id = (SELECT max(message_id) FROM personalmessages  WHERE (receiver_id = ". $talkedto_ordered[$x]. " AND sender_id = $details[1]) OR (receiver_id = $details[1] AND sender_id = ". $talkedto_ordered[$x]. "))";
                    $stmt = $conndb -> prepare($sql);

                    if($stmt ->execute()){
                        $msg_info = $stmt -> fetch();

                        $last_message[$x] = $msg_info['message'];
                        $last_message_timestamp[$x] = $msg_info['unix_timestamp'];
                    }
                    //Indicates whether the last message was sent by the user or not
                    if(strlen($last_message[$x])>=20){
                        if($direction_ordered[$x]=="sent"){
                            $edited_message[$x] =  "You: ".substr($last_message[$x],0,-(strlen($last_message[$x])-20))."...";
                        }else{
                            $edited_message[$x] =  substr($last_message[$x],0,-(strlen($last_message[$x])-20))."...";
                        }
                    }else{
                        if($direction_ordered[$x]=="sent"){
                            $edited_message[$x] = "You: ".$last_message[$x];
                        }else{
                            $edited_message[$x] = $last_message[$x];
                        }
                    }

                    $pm_message[$x] = $edited_message[$x];
                    $msg_available[$x] = 1;

                    //timestamp since last message
                    $tslmessage[$x] = time()-$last_message_timestamp[$x];

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
                    }
                }
            }
        }
    }else{
        $talkedto_ordered = [];
    }
}
?>