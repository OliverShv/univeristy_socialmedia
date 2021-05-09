<?php
require "../php/conn.php";

session_start();
//checks that a user session exists
if(isset($_SESSION['user'])){

    $details = $_SESSION['user'];

    $sql = "UPDATE session SET status = 'inactive' WHERE account_id = :user_id AND generatedCode = :gc";
    $stmt = $conndb->prepare($sql);

        if($stmt->execute(['user_id'=>$details[1],':gc'=>$gc[0]])){
            // remove all session variables
            session_unset();

            // destroy the session
            session_destroy();

            //redirect to login, if due to inactivity, a error is logged
            header("Location: ../index.php");
            exit();

        }else{
            echo "Logout failed";
        }
}else{
    header("Location: ../index.php");
    exit();
}
