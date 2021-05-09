<?php
//Page is used to remove a user or make them admin in a group
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require("../php/conn.php");
    require("../php/verifylogin.php");

    if(isset($_POST["group_id"])&&isset($_POST["user_id"])&&isset($_POST["type"])){

        $group_id = $_POST["group_id"];
        $user_id = $_POST["user_id"];
        $type = $_POST["type"];

        //Checks if the user is the admin of the group
        $sql = "SELECT * FROM groups WHERE group_id = :group_id AND admin_id = :admin_id AND status = 'active'";
        $stmt = $conndb -> prepare($sql);
        $stmt -> execute(["group_id"=>$group_id,"admin_id"=>$details[1]]);
        $total = $stmt -> rowCount();

        if($total!=0){

            //Checks if user is in the group or not
            $sql = "SELECT * FROM groupmembers WHERE user_id = :user_id AND group_id = :group_id AND status='active'";
            $stmt = $conndb -> prepare($sql);
            $stmt -> execute(["user_id"=>$user_id,"group_id"=>$group_id]);
            $total = $stmt -> rowCount();

            if($total!=0){
                if($type=="remove"){
                    //Update the users status to inactive
                    $sql = "UPDATE groupmembers SET status='inactive' WHERE user_id = :user_id AND group_id = :group_id";
                    $stmt = $conndb -> prepare($sql);

                    if($stmt -> execute(["user_id"=>$user_id,"group_id"=>$group_id])){
                        echo json_encode(["type"=>"success", "msg"=>"remove"]);
                    }else{
                        echo json_encode(["type"=>"failed", "msg"=>"Failed to add user"]);
                    }

                }else if($type=="admin"){
                    //Update the users status to inactive
                    $sql = "UPDATE groups SET admin_id = :admin_id WHERE admin_id = :user_id AND group_id = :group_id and status='active'";
                    $stmt = $conndb -> prepare($sql);

                    if($stmt -> execute(["admin_id"=>$user_id,"group_id"=>$group_id,"user_id"=>$details[1]])){
                        echo json_encode(["type"=>"success", "msg"=>"admin"]);
                    }else{
                        echo json_encode(["type"=>"failed", "msg"=>"Failed to change admin"]);
                    }

                }else{
                    echo json_encode(["type"=>"failed", "msg"=>"Unknown command purpose"]);
                }

            }else{
                echo json_encode(["type"=>"failed", "msg"=>"User isn't in the group"]);
            }
        }else{
            echo json_encode(["type"=>"failed", "msg"=>"You are not the admin of the group"]);
        }
    }else{
        echo json_encode(["type"=>"failed", "msg"=>"Lack of information"]);
    }
}else{
    exit();
}

?>