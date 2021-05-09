<?php
    //Page is used to verify the login of a user
    session_start();

    if(isset($_SESSION['user'])){
        //gets the stored session details of the user
        $details = $_SESSION['user'];

        //checks that the session is valid
        $sql = "SELECT * FROM session WHERE generatedCode=:code AND account_id=:id AND status=:status";

        $stmt = $conndb->prepare($sql);
        //if the session isn't valid then the user is taken to the login page
        if($stmt->execute(['code'=>$details[0],'id'=>$details[1],'status'=>'active'])){

            $count = $stmt -> rowCount();

            if($count!=1){
                // remove all session variables
                session_unset();

                // destroy the session
                session_destroy();

                //redirect to login
                header("Location: ../index.php");
                exit();
            }      
        }else{
        // remove all session variables
        session_unset();

        // destroy the session
        session_destroy();

        //redirect to login
        header("Location: ../index.php");
        exit();
        }
    }else{
        header("Location: ../index.php");
        exit();
    }
?>