<?php
//Page is used to leave or delete a group
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require("../php/conn.php");
    require("../php/verifylogin.php");

    if(isset($_POST["group_id"]) && isset($_POST["type"])){

        $group_id = $_POST["group_id"];
        $type = $_POST["type"];

        //Get the admin id of the group
        $sql = "SELECT admin_id FROM groups WHERE group_id = :group_id AND status='active'";
        $stmt = $conndb -> prepare($sql);
        $stmt -> bindParam("group_id",$group_id);

        if($stmt -> execute()){
            //Check that the group exists
            if($stmt -> rowCount() != 0){
                if($stmt -> execute()){
                    $admin_id = $stmt -> fetch()[0];

                    //Set the user to inactive
                    $sql = "UPDATE groupmembers SET status = 'inactive' WHERE user_id = :user_id AND group_id = :group_id";
                    $stmt = $conndb -> prepare($sql);
                    if($stmt -> execute(["user_id"=>$details[1],"group_id"=>$group_id])){
                        //If the user was the admin that allicate a new one
                        if($details[1]== $admin_id){

                            if($type=='leave'){

                                //Get a random active user from the group
                                $sql = "SELECT user_id FROM groupmembers WHERE status='active' AND group_id =:group_id";
                                $stmt = $conndb -> prepare($sql);

                                if($stmt -> execute(["group_id"=>$group_id])){
                                    //if the user is the only one in the group then delete the group
                                    if($stmt -> rowCount()!=0){
                                        if($stmt -> execute()){
                                            $user_id = $stmt -> fetch()[0];

                                            $sql = "UPDATE groups SET admin_id = :user_id WHERE group_id = :group_id AND status='active'";
                                            $stmt = $conndb -> prepare($sql);

                                            if($stmt -> execute(["user_id"=>$user_id,"group_id"=>$group_id])){
                                                echo json_encode(["type"=>"success"]);
                                            }
                                        }
                                    }else{
                                        //Set group to inactive
                                        $sql = "UPDATE groups SET status = 'inactive' WHERE group_id = :group_id";
                                        $stmt = $conndb -> prepare($sql);

                                        if($stmt -> execute(["group_id"=>$group_id])){
                                            echo json_encode(["type"=>"success"]);
                                        }

                                    }
                                }

                            }else if($type=='delete'){

                                //Set all members in the group to inactive
                                $sql = "UPDATE groupmembers SET status = 'inactive' WHERE group_id = :group_id";
                                $stmt = $conndb -> prepare($sql);

                                if($stmt -> execute(["group_id"=>$group_id])){

                                    //Set group to inactive
                                    $sql = "UPDATE groups SET status = 'inactive' WHERE group_id = :group_id";
                                    $stmt = $conndb -> prepare($sql);

                                    if($stmt -> execute(["group_id"=>$group_id])){
                                        echo json_encode(["type"=>"success"]);
                                    }
                                }
                            }
                        }else{
                            echo json_encode(["type"=>"success"]);               
                        }
                    }
                }
            }else{
                echo json_encode(["type"=>"failed", "msg"=>"Group doesn't exist"]);
            }
        }
    }else{
        echo json_encode(["type"=>"failed", "msg"=>"Lack of information"]);
    }
}else{
    exit();
}
?>