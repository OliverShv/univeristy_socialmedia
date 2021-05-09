<?php
//Page adds user to a group
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require("../php/conn.php");
    require("../php/verifylogin.php");

    if(isset($_POST["group_id"])&&isset($_POST["user_id"])){

        $group_id = $_POST["group_id"];
        $user_id = $_POST["user_id"];

        //Checks if user exists
        $sql = "SELECT * FROM accounts WHERE user_id = :user_id";
        $stmt = $conndb -> prepare($sql);
        $stmt -> execute(["user_id"=>$user_id]);
        $total = $stmt -> rowCount(); 

        if($total!=0){

            //Checks if user is already in the group or not
            $sql = "SELECT * FROM groupmembers WHERE user_id = :user_id AND group_id = :group_id AND status='active'";
            $stmt = $conndb -> prepare($sql);
            $stmt -> execute(["user_id"=>$user_id,"group_id"=>$group_id]);
            $total = $stmt -> rowCount();

            if($total==0){

                //Checks if the user adding is the admin of the group
                $sql = "SELECT * FROM groups WHERE group_id = :group_id AND admin_id = :admin_id AND status = 'active'";
                $stmt = $conndb -> prepare($sql);
                $stmt -> execute(["group_id"=>$group_id,"admin_id"=>$details[1]]);
                $total = $stmt -> rowCount();

                if($total!=0){

                    //Checks if the user was in the group at a point
                    $sql = "SELECT * FROM groupmembers WHERE group_id = :group_id AND user_id = :user_id AND status = 'inactive'";
                    $stmt = $conndb -> prepare($sql);
                    $stmt -> execute(["group_id"=>$group_id,"user_id"=>$user_id]);
                    $total = $stmt -> rowCount();

                        //sets user back to active
                    if($total!=0){

                        $sql = "UPDATE groupmembers SET status = 'active' WHERE user_id = :user_id and group_id = :group_id";
                        
                    }else{
                        //Adds the user to the database
                        $sql = 'INSERT INTO groupmembers (group_id,user_id,status)
                        VALUES (:group_id,:user_id,"active")';
                    }

                    $stmt = $conndb -> prepare($sql);

                    if($stmt -> execute(["group_id"=>$group_id,"user_id"=>$user_id])){
                        echo json_encode(["type"=>"success"]);
                    }else{
                        echo json_encode(["type"=>"failed", "msg"=>"Failed to add user"]);
                    }

                }else{
                    echo json_encode(["type"=>"failed", "msg"=>"You are not the admin of this group"]);
                }
            }else{
                echo json_encode(["type"=>"failed", "msg"=>"User is already in the group"]);
            }
        }else{
            echo json_encode(["type"=>"failed", "msg"=>"User doesn't exist"]);
        }
    }else{
        echo json_encode(["type"=>"failed", "msg"=>"Lack of information"]);
    }
}else{
    exit();
}