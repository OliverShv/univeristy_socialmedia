<?php
//Page is used to create a new option to view a group chat
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require("../php/conn.php");
    require("../php/verifylogin.php");

    if(isset($_POST["id"])){

        //Admin and group name are collected
        $group_id[0] = $_POST["id"];
        $sql = "SELECT name, admin_id FROM groups WHERE group_id = :group_id";
        $stmt = $conndb -> prepare($sql);

        if($stmt -> execute(["group_id"=>$group_id[0]])){
            $info = $stmt -> fetch();
            $group_name[0] = $info[0];
            $admin_id[0] = $info[1];
        }else{
            $group_name[0] = "<i>Couldn't receive name</i>";
            $admin_id[0] = "";
        }

        //Information of the last message is collected
        $sql = "SELECT sender_id, message, unix_timestamp FROM groupmessages WHERE group_id = :group_id ORDER BY message_id DESC";
        $stmt = $conndb -> prepare($sql);

        if($stmt -> execute(["group_id"=>$group_id[0]])){
            $total_msg = $stmt -> rowCount();

            if($total_msg==0){

                $msg_available[0] = 1;
                $displayedTime[0] = "";
                $group_message[0] = "<i>no messages</i>";

            }else if($stmt -> execute(["group_id"=>$group_id[0]])){
                $msg = $stmt -> fetch();

                $msg_available[0] = 1;
                $group_message[0] = $msg["message"];

                //timestamp since last message
                $tslmessage[0] = time()-$msg["unix_timestamp"];

                if($tslmessage[0]==1){
                    $displayedTime[0] = $tslmessage[0]. " second";
                }else if($tslmessage[0]<60*2){
                    $displayedTime[0] = $tslmessage[0]. " seconds";
                }else if($tslmessage[0]<(60*60*2)){
                    $displayedTime[0] = floor($tslmessage[0]/60). " minutes";
                }else if($tslmessage[0]<(60*60*24*2)){
                    $displayedTime[0] = floor($tslmessage[0]/(60*60)). " hours";
                }else if($tslmessage[0]<(60*60*24*7*2)){
                    $displayedTime[0] = floor($tslmessage[0]/(60*60*24)). " days";
                }else if($tslmessage[0]<(60*60*24*52*2)){
                    $displayedTime[0] = floor($tslmessage[0]/(60*60*24*7)). " weeks";
                }else if($tslmessage[0]>=(60*60*24*7*52)){
                    $displayedTime[0] = "Over a year";
                }else{
                    $displayedTime[0] = "";
                }
            }
        }else{
            $msg_available[0] = 1;
            $displayedTime[0] = "";
            $group_message[0] = "<i>error</i>";
        }

        //Total users in the group is collected
        $sql = "SELECT DISTINCT account_id FROM connections INNER JOIN session ON connections.session_id = session.session_id WHERE chat_id= :chat_id AND connections.status = 'group'";
        $stmt = $conndb -> prepare($sql);
        $stmt -> bindParam(":chat_id", $group_id[0]);

        if($stmt -> execute()){
            $group_inchat[0] = $stmt ->rowCount();
        }else{
            $group_inchat[0] = 0;
        }

        //Group template is pasted with collected info
        $x = 0;
        require "group-template.php";

    }
}
?>