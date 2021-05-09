<?php
//Logs that messages have been seen in teh database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'conn.php';
        
    if(isset($_POST["user_id"])&&isset($_POST["sender_id"])){
        $user_id = $_POST["user_id"];
        $sender_id = $_POST["sender_id"];

        $sql = "UPDATE personalmessages SET seen='yes' WHERE receiver_id=:receiver_id AND sender_id=:sender_id";
        $stmt = $conndb -> prepare($sql);
        if($stmt ->execute(["receiver_id"=>$user_id,"sender_id"=>$sender_id])){
            return true;
        }else{
            return false;
        }
    };
}
?>