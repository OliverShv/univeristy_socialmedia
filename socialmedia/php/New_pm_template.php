<?php
//Page is used to create a new option to view a personal  chat
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require("../php/conn.php");
    require("../php/verifylogin.php");

    if(isset($_POST["id"])&&isset($_POST["type"])&&isset($_POST["msg"])){

        $id = $_POST["id"];
        $type = $_POST["type"];
        $msg = $_POST["msg"];

        //Other users information is collected
        $sql = "SELECT fname, lname, picture, cyear FROM accounts WHERE user_id = $id";
        $stmt = $conndb -> prepare($sql);

        if($stmt -> execute()){
            $user = $stmt -> fetch();

            $pm_fname[0] = $user["fname"];
            $pm_lname[0] = $user["lname"];
            $pm_picture[0] = $user["picture"];
            $pm_cyear[0] = $user["cyear"];

            //Used to style the year they are in
            if($pm_cyear[0]==1){
                $pm_cyear[0]="st year";
            }else if($pm_cyear[0]==2){
                $pm_indicator[0]="nd year";
            }else if($pm_cyear[0]==3){
                $pm_indicator[0]="rd year";
            }else if ($pm_cyear[0]==null){
                $pm_indicator[0]="";
            }else{
                $pm_indicator[0]="th year";
            }

            //Other users course is collected
            $sql = "SELECT cname FROM courses WHERE course_id = (SELECT course_id FROM studentcourse WHERE student_id =$id)";
            $stmt = $conndb -> prepare($sql);
            $stmt -> execute();
            if($stmt -> rowCount()>0){
                $stmt -> execute();
                $pm_course[0] = $stmt -> fetch()["cname"];
            }else{
                $pm_course[0] = "";
            }

            if($type=="sender"){
                $total_msg[0] = 0;
            }else{
                $total_msg[0] = 1;
            }

            //Message previes are limtied ot 20 characters
            if(strlen($msg)>=20){
                if($type=="sender"){
                    $pm_message[0] =  "You: ".substr($msg,0,-(strlen($msg)-20))."...";
                }else{
                    $pm_message[0] =  "<b>".substr($msg,0,-(strlen($msg)-20))."...</b>";
                }
            }else{
                if($type=="sender"){
                    $pm_message[0] = "You: ".$msg;
                }else{
                    $pm_message[0] = "<b>".$msg."</b>";
                }
            }
        }

        $talkedto_ordered[0] = $id;
        $onlineStatus[0] = "online";
        $totalunseen[0] = 1;
        $msg_available[0] =1;
        $displayedTime[0] = "now";

        $x=0;
        //personal message template is pasted with collected info
        require "pm-template.php";
    }
}
?>