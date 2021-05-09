<?php
//Turns text into  html entities
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST["msg"])) {
    $altered_msg = htmlentities($_POST["msg"]);

    echo $altered_msg;
    }
}
?>