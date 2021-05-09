<?php

//Page sends information for the chat header for personal messages
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require("../php/conn.php");
    require("../php/verifylogin.php");

    if(isset($_POST["receiver_id"])){
        $id = $_POST["receiver_id"];

        //Checks whether the user is online in the chat area or not
        $sql = "SELECT connection_id FROM connections WHERE session_id in(
        SELECT session_id FROM session WHERE status = 'active' AND account_id = :id)
        AND status='active'";

        $stmt = $conndb -> prepare($sql);
        $stmt -> execute(["id"=>$id]);
        $totalActiveClients = $stmt-> rowCount();

        if($totalActiveClients==0){
            $onlineStatus = "offline";
        }else{
            $onlineStatus = "online";
        }

        //Collected the users forename, surname and picture
        $sql = "SELECT fname, lname, picture FROM accounts WHERE user_id= :id";
        $stmt = $conndb -> prepare($sql);
        $stmt -> execute(["id"=>$id]);

        $user =  $stmt -> fetch();

        //users forename and surname are combined
        $name = ucfirst($user["fname"])." ".ucfirst($user["lname"]);
        $picture = $user["picture"];

        echo json_encode(array("picture"=>$picture,"name"=>$name,"status"=>$onlineStatus));
    }else{
        exit();
    }
}else{
    exit();
}
?>