<?php
//This page retieves a users information as well as updates it
//Get profile details
$sql = "SELECT fname, lname, picture, bio, password, salt FROM accounts WHERE user_id = :id";
$stmt = $conndb->prepare($sql);

if($stmt->execute(["id"=>$details[1]])){

    $user = $stmt ->fetch();
        $fname = $user['fname'] ?? "";
        $lname = $user['lname'] ?? "";
        $picture = $user['picture'] ?? "default";
        $bio = $user['bio'] ?? "";
        $pswEncrypted = $user['password'];
        $salt = $user['salt'];
}else{
    header("Location: profile.php?error=error01");
    exit();
}


//Update details
if($_SERVER['REQUEST_METHOD']=="POST"){
    if(isset($_POST["forename"])&&isset($_POST["surname"])){

        //Checks that a users name is in the right format
        if(!preg_match("/^[A-Za-z ,.'-]{1,35}$/",$_POST["forename"])){
            $error = "Forename is invalid";
        }
        
        if(!preg_match("/^[A-Za-z ,.'-]{1,35}$/",$_POST["surname"])){
            $error = "Surname is invalid";
        }
        //Turns there name into html entities
        $forename = htmlentities($_POST["forename"]);
        $surname = htmlentities($_POST["surname"]);
        //Gets the status of the image
        $imageStatus = $_POST["imageStatus"];
        
        //If teh bio has been changed then it is checked that it is in the right format
        if(isset($_POST["bio"])){

            $newBio = htmlentities($_POST["bio"]);

            if(empty($newBio)){
                $newBio = null;

            }else{
                if(strlen($_POST["bio"])>160){
                    $error = "A bio can only have 160 characters maximum";
                    $newBio = htmlentities($bio);
                }
            }
        }else{
            $newBio = null;
        }

        //If the image has been changed this runs
        if($imageStatus=="changed"){

            //Informaion of the fiel is retireved
            $file = $_FILES["profilepic"];

            $fileName = $file["name"];
            $fileTmpName = $file["tmp_name"];
            $fileError = $file["error"];
            $fileSize = $file["size"];
            $fileType = $file["type"];
            
            //File name is split to get its extension
            $fileExt = explode('.',$fileName);
            $fileActualExt = strtolower(end($fileExt));
            
            //Only extensions allowed
            $allowed = array('jpg','jpeg','png');
                    
            if(in_array($fileActualExt,$allowed)){
                if($fileError===0){
                    //25mb in bytes
                    if($fileSize < 26214400){
                        //Image is renamed
                        $newPicture = $details[1].".".uniqid('',TRUE).".".$fileActualExt;
                        //Image location is set
                        $fileDestination = '../img/profilepics/'.$newPicture;
                        //Image is moved to that location
                        move_uploaded_file($fileTmpName,$fileDestination);
                        if($picture!="default.png"){
                            //Old image is deleted
                            unlink('../img/profilepics/'.$picture);
                        }
                    }else{
                        $newPicture = $picture;
                        $error = "Your file must be udner 25mb!";
                    }
                }else{
                    $newPicture = $picture;
                    $error = "There was an error uploading your file!";
                }
            }else{
                $newPicture = $picture;
                $error = "You cannot upload files of this type!";
            }
        //If the image is deleted then it is removed from the files
        }else if($imageStatus=="deleted"){
            $newPicture ="default.png";
            if($picture!="default.png"){
                unlink('../img/profilepics/'.$picture);
            }
        }else if($imageStatus=="same"){
            $newPicture = $picture;
        }else{
            $newPicture ="default.png";
        }
        //If the passwords were changed, they are checked to make sure they match and are in teh right format
        if(isset($_POST['pass1'])&&isset($_POST['pass2'])){
            if(!empty($_POST['pass1'])&&!empty($_POST['pass2'])){
                if($_POST['pass1']==$_POST['pass2']){
                    $psw = $_POST['pass2'];

                    if(!preg_match("/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/",$psw)){
                        $error = "Password is invalid";
                    }
                    
                    //New password is encrypted
                    $salt = uniqid(false);
                    $pepper = mt_rand(0, 25);
                    $newpass = $psw.$salt.$pepper;

                    $pswEncrypted = password_hash($newpass, PASSWORD_DEFAULT);
                }else{
                    $error = "Passwords need to match";
                }
            }
        }
        
        //If there are not errors the new user information is inserted into the database
        if(!isset($error)){
            $sql = "UPDATE accounts
            SET fname = :forename, lname= :surname, bio= :bio, picture= :picture, password =:password, salt =:salt
            WHERE user_id = :id";

            $stmt = $conndb -> prepare($sql);

            if($stmt->execute(["forename"=>$forename,"surname"=>$surname,"bio"=>$newBio,"picture"=>$newPicture,"password"=>$pswEncrypted,"salt"=>$salt,"id"=>$details[1]])&&!isset($error)){            
                header("Location: settings.php?update=successful");
                exit();
            }else{
                $error = "Update failed, try again";
            }
        }

    }else{
        $error = "Forename and surname need to be set";
    }

}
?>