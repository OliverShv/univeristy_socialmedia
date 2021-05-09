<?php
//Page is used to register a user
//Makes sure the HTTP request was through a POST method
if($_SERVER['REQUEST_METHOD']=="POST"){
	//Redirect user back to register page if a field is empty
	if(!empty($_POST["id"]) && !empty($_POST["forename"]) && !empty($_POST["surname"]) && !empty($_POST["email"]) && !empty($_POST["pass1"]) && !empty($_POST["pass2"])){

		//POST variables store din local ones
		$id = $_POST["id"];
		$email = strtolower($_POST["email"]);
		$forename = strtolower($_POST["forename"]);
		$surname = strtolower($_POST["surname"]);
		$pass1 = $_POST["pass1"];
		$pass2 = $_POST["pass2"];

		//Current time
		$unix_timestamp = time();

		//Password encyption
		$salt = uniqid(false);
		$pepper = mt_rand(0, 25);
		$newpass = $pass1.$salt.$pepper;

		$encyptpass = password_hash($newpass, PASSWORD_DEFAULT);

		//Redirect user back to register page if a field is empty
		if(empty($id) || empty($email) || empty($forename) || empty($surname) || empty($pass1) || empty($pass2)){
			header("Location: register.php?error=error01&id=".$id."&email=".$email."&forename=".$forename."&surname=".$surname);
			exit();
		}
		//Redirect user back to register page if the email is in the wrong format
		else if(!preg_match("/[a-z0-9_]+@uni.worc.ac.uk/",$email)){
			header("Location: register.php?error=error02&id=".$id."&forename=".$forename."&surname=".$surname);
			exit();
		}
		//Redirect user back to register page if the id is in the wrong format
		else if(!preg_match("/^[0-9]{8}$/",$id)){
			header("Location: register.php?error=error03&email=".$email."&forename=".$forename."&surname=".$surname);
			exit();
		}
		//Redirect user back to register page if their forename is in the wrong format
		else if(!preg_match("/^[A-Za-z ,.'-]{1,35}$/",$forename)){
			header("Location: register.php?error=error04&id=".$id."&email=".$email."&forename=".$forename);
			exit();
		}
		//Redirect user back to register page if their surname is in the wrong format
		else if(!preg_match("/^[A-Za-z ,.'-]{1,35}$/",$surname)){
			header("Location: register.php?error=error05&id=".$id."&email=".$email."&surname=".$surname);
			exit();
		}
		//Redirect user back to register page if both passwords don't match
		else if($pass1 !== $pass2){
			header("Location: register.php?error=error06&id=".$id."&email=".$email."&forename=".$forename."&surname=".$surname);
			exit();
		}
		//Redirect user back to register page if their password is in teh worng format
		else if(!preg_match("/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/",$pass1)){
			header("Location: register.php?error=error07&id=".$id."&email=".$email."&forename=".$forename."&surname=".$surname);
			exit();
		}else{

			//Check if the ID already exists
			$sql = "SELECT * FROM accounts where user_id = :user_id";
			$stmt = $conndb->prepare($sql);
			$stmt -> bindParam(":user_id", $id);

			$stmt -> execute();
			$totalUserIds = $stmt -> rowCount();

			echo $totalUserIds;

			//Check if email exists
			$sql = "SELECT * FROM accounts where email = :email";
			$stmt = $conndb->prepare($sql);
			$stmt -> bindParam(":email", $email);

			$stmt -> execute();
			$totalUserEmails = $stmt -> rowCount();

			if($totalUserIds!=0){
				header("Location: register.php?error=error08&email=".$email."&forename=".$forename."&surname=".$surname);
				exit();
			}else if($totalUserEmails!=0){
				header("Location: register.php?error=error09&id=".$id."&forename=".$forename."&surname=".$surname);
				exit();
			}else{

				//User information is inserted into the database
				$sql = "INSERT INTO accounts (user_id, email, fname, lname, password, salt, unix_timestamp)
					VALUES (:user_id, :email, :forename, :surname, :password, :salt, :unix_timestamp)";

				$stmt = $conndb->prepare($sql);

				if ($stmt->execute(['user_id'=>$id, 'email'=>$email, 'forename'=>$forename, 'surname'=>$surname, 'password'=>$encyptpass, 'salt'=>$salt, 'unix_timestamp'=>$unix_timestamp])){
					header("Location: index.php?email=".$email);
					exit();
				}else{
					header("Location: register.php?error=error10&id=".$id."&email=".$email."&forename=".$forename."&surname=".$surname);
					exit();
				exit();
				}
			}
		}
	}else{
		header("Location: register.php?error=error01&id=".$_POST["id"]."&email=".$_POST["email"]."&forename=".$_POST["forename"]."&surname=".$_POST["surname"]);
		exit();
	}
}
?>