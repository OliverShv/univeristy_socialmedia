<?php 
//Page is used to fetch chats from a personal chat
if($_SERVER['REQUEST_METHOD'] === 'POST') {

    require("../php/conn.php");
    require("../php/verifylogin.php");

    date_default_timezone_set("Europe/London");

    if(isset($_POST["receiver_id"])){
        $receiver_id = $_POST["receiver_id"];

        //If the receiver aren't already assigned a colour then one is generated for them
        if(isset($_POST["color"])){
            $color = $_POST["color"];
        }else{
            $color = rand(50,125).",".rand(50,125).",".rand(50,125);
        }

        //If a number of messages to load isn't asked for then 20 will be loaded
        if(isset($_POST["totalmessages"])){
            $totalmessages = $_POST["totalmessages"];
        }else{
            $totalmessages = 20;
        }

        //Gets the receivers name
        $sql = "SELECT fname, lname FROM accounts WHERE user_id =".$receiver_id;
            $stmt = $conndb -> prepare($sql);

            if($stmt ->execute()){
                $name = $stmt -> fetch();
                $fname = $name['fname'];
                $lname = $name['lname'];

            }
        
            //Information for each message is put into arrays
            $sql ="SELECT message_id, sender_id, message, unix_timestamp from personalmessages WHERE (receiver_id = $receiver_id AND sender_id = $details[1]) OR (receiver_id = $details[1] AND sender_id = $receiver_id)  ORDER BY unix_timestamp DESC LIMIT $totalmessages";
            $stmt = $conndb -> prepare($sql);
            $stmt ->execute();
            $message_id = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);
            $stmt ->execute();
            $sender_id = $stmt -> fetchAll(PDO::FETCH_COLUMN, 1);
            $stmt ->execute();
            $message = $stmt -> fetchAll(PDO::FETCH_COLUMN, 2);
            $stmt ->execute();
            $unix_timestamp = $stmt -> fetchAll(PDO::FETCH_COLUMN, 3);


            //The arrays are reversed so the messages are loaded from oldest to newest
            $a = array_reverse($sender_id);
            $b = array_reverse($message);
            $c = array_reverse($unix_timestamp);
            $d = array_reverse($message_id);

        echo "<span id='color' style='display:none'>".$color."</span>";

         //Messages are loaded
        for($x=0;$x<sizeof($a);$x++){
            
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
                echo "<span onmouseover='".$showdate."' onmouseout='".$hidedate."'><a href='profile.php?user=$receiver_id' style='color:rgb($color)'><b>".ucfirst($fname)." ".ucfirst($lname)."<a/></b><span id='message".$d[$x]."' style='display:none;color:rgb($color)'> ".date('jS F Y, h:i A',$c[$x])."</span><br>".$b[$x]."</span><br><br>";
            }

        }
    }
}
?>