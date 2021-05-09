<?php 
//Page is used to fetch chats from a group
if($_SERVER['REQUEST_METHOD'] === 'POST') {

    require("../php/conn.php");
    require("../php/verifylogin.php");

    date_default_timezone_set("Europe/London");

    if(isset($_POST["receiver_id"])){

        $group_id = $_POST["receiver_id"];

        //If a number of messages to load isn't asked for then 20 will be loaded
        if(isset($_POST["totalmessages"])){
            $totalmessages = $_POST["totalmessages"];
        }else{
            $totalmessages = 20;
        }

         //Information for each message is put into arrays
        $sql ="SELECT message_id, sender_id, message, groupmessages.unix_timestamp, fname, lname, user_id from groupmessages INNER JOIN accounts ON groupmessages.sender_id = accounts.user_id WHERE group_id = :group_id ORDER BY unix_timestamp DESC LIMIT $totalmessages";
        $stmt = $conndb -> prepare($sql);
        $stmt -> bindParam(":group_id", $group_id);

        $stmt ->execute();
        $message_id = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);

        $stmt ->execute();
        $sender_id = $stmt -> fetchAll(PDO::FETCH_COLUMN, 1);

        $stmt ->execute();
        $message = $stmt -> fetchAll(PDO::FETCH_COLUMN, 2);

        $stmt ->execute();
        $unix_timestamp = $stmt -> fetchAll(PDO::FETCH_COLUMN, 3);

        $stmt ->execute();
        $fname = $stmt -> fetchAll(PDO::FETCH_COLUMN, 4);

        $stmt ->execute();
        $lname = $stmt -> fetchAll(PDO::FETCH_COLUMN, 5);

        $stmt ->execute();
        $user_id = $stmt -> fetchAll(PDO::FETCH_COLUMN, 6);

         //The arrays are reversed so the messages are loaded from oldest to newest
        $a = array_reverse($sender_id);
        $b = array_reverse($message);
        $c = array_reverse($unix_timestamp);
        $d = array_reverse($message_id);
        $e = array_reverse($fname);
        $f = array_reverse($lname);
        $g = array_reverse($user_id);

        $unqiueId = array_unique($a);

        //If users aren't already assigned a colour then one is generated for them
        if(isset($_POST["colors"])){

            $colors = $_POST["colors"];

            foreach($unqiueId as $value){

                if(!isset($colors[$value])){
                    $colors[$value] =  rand(50,125).",".rand(50,125).",".rand(50,125);
                }
            }

        }else{

            foreach($unqiueId as $value){
                $colors[$value] =  rand(50,125).",".rand(50,125).",".rand(50,125);
            }

        }


        //Information bout the colors and users they belong to are stored in the chatroom
        if(!empty($a)){
            echo "<script> var colors =".json_encode($colors)."</script>";
        }else{
            echo "<script> var colors =[] </script>";
        }   

        //Messages are loaded
        for($x=0;$x < count($a); $x++){
            
            if($x>0){

                $datediff = floor($c[$x]/ (60 * 60 * 24)) - floor($c[$x-1]/ (60 * 60 * 24));

                if($datediff>0){
                    echo "<span style='text-align:center;display: block'><b>".date('jS F Y',$c[$x])."</b></span><br><br>";
                }
            }else{
                    echo "<span style='text-align:center;display: block'><b>".date('jS F Y',$c[$x])."</b></span><br><br>";
            }

             //Loads a message in two formats depending on if the message belongs to the user or not
            if($a[$x]==$details[1]){
                $showdate = 'showDate("message'.$d[$x].'")';
                $hidedate = 'hideDate("message'.$d[$x].'")';
                echo "<span onmouseover='".$showdate."' onmouseout='".$hidedate."' style='text-align:right;display: block'><span id='message".$d[$x]."' style='display:none;'>".date('jS F Y, h:i A',$c[$x])."</span><b> You</b><br>".$b[$x]."</span><br>";
            }else{
                $showdate = 'showDate("message'.$d[$x].'")';
                $hidedate = 'hideDate("message'.$d[$x].'")';
                echo "<span onmouseover='".$showdate."' onmouseout='".$hidedate."'><a href='profile.php?user=".$g[$x]."' style='color:rgb(".$colors[$a[$x]].")'><b>".ucfirst($e[$x])." ".ucfirst($f[$x])."<a/></b><span id='message".$d[$x]."' style='display:none;color:rgb(".$colors[$a[$x]].")'> ".date('jS F Y, h:i A',$c[$x])."</span><br>".$b[$x]."</span><br><br>";
            }

        }
    }
}

?>