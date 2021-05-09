<?php
//Page is used to fetch a groups users
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require("../php/conn.php");
    require("../php/verifylogin.php");

    if(isset($_POST["group_id"])){

        $users = array();
        $group_id = $_POST["group_id"];

        //Get admin of the group
        $sql = "SELECT admin_id from GROUPS WHERE group_id = :group_id";
        $stmt = $conndb -> prepare($sql);
        $stmt -> bindParam("group_id",$group_id);
        $stmt ->execute();
        $admin_id = $stmt ->fetch()[0];

        //Checks if user is the admin
        if($admin_id==$details[1]){
            $isAdmin = " (Admin)";
        }else{
            $isAdmin = "";
        }
        
        //get user details
        $sql = "SELECT fname, lname FROM accounts WHERE user_id = :account_id";
        $stmt = $conndb -> prepare($sql);
        $stmt -> bindParam("account_id",$details[1]);

        //Show user information first
        if($stmt -> execute()){
            $user = $stmt -> fetch();
            echo "<a href='../online/profile.php?user=".$details[1]."'>".ucfirst($user[0])." ".ucfirst($user[1]).$isAdmin."</a><br>";
        }

        //get group users details
        $sql = "SELECT user_id FROM groupmembers WHERE group_id = :group_id AND status='active'";
        $stmt = $conndb -> prepare($sql);
        $stmt -> bindParam("group_id",$group_id);
        
        if($stmt -> execute()){
            $user_id = $stmt ->fetchAll(PDO::FETCH_COLUMN, 0);

            $sql = "SELECT fname, lname FROM accounts WHERE user_id = :account_id";
            $stmt = $conndb -> prepare($sql);
            
            for($x=0;$x<count($user_id);$x++){
                $stmt -> execute(["account_id"=>$user_id[$x]]);
                $users[$x][0] = $user_id[$x];
                $users[$x][1] = $stmt -> fetch()[0];
                $stmt -> execute(["account_id"=>$user_id[$x]]);
                $users[$x][2] = $stmt -> fetch()[1];
            }

            //Get group members that are online
            $sql = "SELECT connection_id FROM connections WHERE session_id IN (
                SELECT session_id FROM session WHERE account_id = :user_id AND status='active'
                ) AND status='active' AND type='group' AND chat_id=:group_id";
            $stmt = $conndb -> prepare($sql);

            for($x=0;$x<count($user_id);$x++){

                $stmt -> execute(["group_id"=>$group_id,"user_id"=>$user_id[$x]]);

                if(($stmt -> rowCount()!=0)){
                    $users[$x][3] = "online";
                }else{
                    $users[$x][3] = "offline";
                }

            }

            //Display admin control options with name of group members
            if($admin_id==$details[1]){

                for($x=0;$x<count($user_id);$x++){
                    if($user_id[$x]!=$details[1]){
                        echo "<div class='row' style='margin-bottom:1px;'><div class='col-8'><a href='../online/profile.php?user=".$users[$x][0]."'>".ucfirst($users[$x][1])." ".ucfirst($users[$x][2])."  </a><span id='onlineStatus".$users[$x][0]."' class='".$users[$x][3]."'></span></div><div class='col-2'><button class='promptSmallButton' onclick='controlGroup(".$users[$x][0].",".$group_id.",\"remove\")'><img style='width:25px;height:25px' src='../img/door_icon.png'></button></div><div class='col-2'><button class='promptSmallButton' onclick='controlGroup(".$users[$x][0].",".$group_id.",\"admin\")'><img style='width:25px;height:25px' src='../img/crown_icon.png'></button></div></div>";
                    }
                }

            }else{
                //Display just names of group members
                for($x=0;$x<count($user_id);$x++){

                    if($admin_id==$user_id[$x]){
                        $isAdmin = " (Admin)";
                    }else{
                        $isAdmin = "";
                    }

                    if($user_id[$x]!=$details[1]){
                        echo "<div class='row' style='margin-bottom:1px;'><div class='col'><a href='../online/profile.php?user=".$users[$x][0]."'>".ucfirst($users[$x][1])." ".ucfirst($users[$x][2]).$isAdmin."  </a><span id='onlineStatus".$users[$x][0]."' class='".$users[$x][3]."'></span></div></div>";
                    }

                }
            }
        }
    }
}else{
    header("../online/groupchat.php");
    exit();
}