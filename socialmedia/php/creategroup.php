<?php
//Page is used to create a group
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require("../php/conn.php");
    require("../php/verifylogin.php");

    //Verify name is not empty and follows regex pattern
    if(isset($_POST["name"])){
        $name = $_POST["name"];

        if(preg_match("/^[A-Za-z0-9 ]{1,20}$/",$name)){

            $time = time();
            $user_id = $details[1];

            $sql = "INSERT INTO groups (name, admin_id, status, unix_timestamp)
            VALUES (:name, :id, 'active', :unix_timestamp)";

            //Insert group and get group id
            $stmt = $conndb -> prepare($sql);
            if($stmt -> execute(["name"=>$name,"id"=>$user_id,"unix_timestamp"=>$time])){

                $sql = "SELECT group_id FROM groups ORDER BY group_id DESC";
                $stmt = $conndb -> prepare($sql);

                if($stmt -> execute()){
                    $group_id = $stmt ->fetch()[0];
                    //Insert user as a member of the group
                    $sql = "INSERT INTO groupmembers (group_id, user_id, status)
                    VALUES (:group_id, :id, 'active')";

                    $stmt = $conndb -> prepare($sql);
                    $stmt -> execute(["group_id"=>$group_id,"id"=>$user_id]);

                    //Success and error messages below
                    $array = ["type"=>"success","msg"=>"Group created","group_id"=>$group_id];
                    echo json_encode($array);
                }else{
                    $array = ["type"=>"error","msg"=>"Partial fail, refresh page"];
                    echo json_encode($array);
                }
            }else{
                $array = ["type"=>"error","msg"=>"Group failed to be created"];
                echo json_encode($array);
            }
        }else{
            $array = ["type"=>"error","msg"=>"Invalid name"];
            echo json_encode($array);
        }
    }else{
        $array = ["type"=>"error","msg"=>"Name not set"];
        echo json_encode($array);
    }
}else{
    $array = ["type"=>"error","msg"=>"Wrong Request type"];
    echo json_encode($array);
}

?>