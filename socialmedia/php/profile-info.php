<?php
//Page is used to retrieve a users profile information
//If no id was selected then use the users
if(!isset($_GET["user"])){
    $profile = $details[1];
}else{
    $profile = $_GET["user"];
}

//Searchs for user with that id
$sql = "SELECT user_id FROM accounts WHERE user_id = :id";
$stmt = $conndb->prepare($sql);

if($stmt->execute(['id'=>$profile])){
    $count = $stmt -> rowCount();

    //sends user to their profile if profile searched for doesn't exist
	if($count==0){
		header("Location: profile.php");
		exit();
	}else{

        //Collects user information
        $sql = "SELECT fname, lname, cyear, picture, bio FROM accounts WHERE user_id = :id";
        $stmt = $conndb->prepare($sql);

        if($stmt->execute(['id'=>$profile])){

            $user = $stmt->fetch();

            $fname = $user['fname'] ?? "N/A";
            $lname = $user['lname'] ?? "N/A";
            $cyear = $user['cyear'] ?? "N/A";
            $picture = $user['picture'] ?? "default";
            $bio = $user['bio'] ?? "No bio available";

            //full name
            $fullname = ucfirst($fname). " " . ucfirst($lname);

            if(isset($_GET["user"])){
                if($_GET["user"]!=$details[1]){
                    $profile_id = $_GET["user"];
                    $messageoption = '<a href="privatechat.php?pm='.$profile_id.'"><img style="width:40px" src="../img/message_icon.png"></a>';
                }
            }

            $sql = "SELECT cname FROM courses INNER JOIN studentcourse on courses.course_id = studentcourse.course_id WHERE student_id = :id";

            $stmt = $conndb->prepare($sql);
            if($stmt->execute(['id'=>$profile])){
                if($stmt->rowCount() > 0){
                    //Get users course name
                    $user = $stmt->fetch();
                    $cname = $user['cname'];
                    
                    $sql = "SELECT mname FROM modules INNER JOIN studentmodule on modules.module_id = studentmodule.module_id WHERE student_id = :id";
                    $stmt = $conndb->prepare($sql);
                    
                    if($stmt->execute(['id'=>$profile])){
                        if($stmt->rowCount() > 0){
                            $modules = $stmt->fetchAll();
                        }else{
                            $mname = NULL;
                        }
                    }else{
                        $mname = NULL;
                    }
                }else{
                    $cname = NULL;
                }
            }else{
                $cname = NULL;
            }
        }
    }
}
?>