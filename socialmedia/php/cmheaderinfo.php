<?php
//Page sends information for the chat header for course/modules
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    require("../php/conn.php");
    require("../php/verifylogin.php");

    if(isset($_POST["receiver_id"])){
        $id = $_POST["receiver_id"];

        //Get module/course name
        if(strlen($id)==4){

            $sql = "SELECT cname FROM courses WHERE course_id= :id";
            $stmt = $conndb -> prepare($sql);
            $stmt -> execute(["id"=>$id]);
            $name =  $stmt -> fetch()["cname"];

        }else if(strlen($id)==8){

            $sql = "SELECT mname FROM modules WHERE module_id= :id";
            $stmt = $conndb -> prepare($sql);
            $stmt -> execute(["id"=>$id]);
            $name =  $stmt -> fetch()["mname"];

        }
         //Get Total users in the chat
        $sql = "SELECT DISTINCT account_id FROM connections INNER JOIN session ON connections.session_id = session.session_id WHERE chat_id= :chat_id AND connections.status = 'active'";
        $stmt = $conndb -> prepare($sql);
        $stmt -> bindParam(":chat_id", $id);
        $stmt -> execute();
        $total = $stmt ->rowCount();

        echo json_encode(array("name"=>$name,"total"=>$total));
    }else{
        exit();
    }
}else{
    exit();
}
?>