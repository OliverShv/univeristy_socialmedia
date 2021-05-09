<?php
//Gets header for the groups
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'conn.php';
    if(isset($_POST["receiver_id"])) {
    $id = $_POST["receiver_id"];
    
    //Name of group
    $sql = "SELECT name FROM groups WHERE group_id= :id";
    $stmt = $conndb -> prepare($sql);
    $stmt -> execute(["id"=>$id]);
    $name =  $stmt -> fetch()["name"];

    //Number of people in the group
    $sql = "SELECT DISTINCT account_id FROM connections INNER JOIN session ON connections.session_id = session.session_id WHERE chat_id= :chat_id AND connections.status = 'group'";
    $stmt = $conndb -> prepare($sql);
    $stmt -> bindParam(":chat_id", $id);
    $stmt -> execute();
    $total = $stmt ->rowCount();

    echo json_encode(array("name"=>$name,"total"=>$total));
    }
}
?>