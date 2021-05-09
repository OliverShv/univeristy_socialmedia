<?php
//Page is used to login a user
require "conn.php";
session_start();

if($_SERVER['REQUEST_METHOD']=="POST"){

    if(!empty($_POST["email"]) && !empty($_POST["pass"])){
	
		$email = $_POST["email"];
        $pass = $_POST["pass"];
		$unix_timestamp = time();
		
		//Redirect user back to register page if the email is in the wrong format
		if(!preg_match("/[a-z0-9_]+@uni.worc.ac.uk/",$email)){
			header("Location: ../index.php?error=failedlogin&email=".$email);
			exit();
		}else if(!preg_match("/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/",$pass)){
			header("Location: ../index.php?error=failedlogin&email=".$email);
			exit();
		}else{
	
			$sql = "SELECT * FROM accounts where email = :email";
            $stmt = $conndb->prepare($sql);
            $stmt -> bindParam(":email", $email);

            $stmt -> execute();
            $total = $stmt -> rowCount();

			if($total!=1){
				header("Location: ../index.php?error=failedlogin&email=".$email);
				exit();
			}else{
				//Get user information associated with the email
				$sql = "SELECT user_id, password, salt FROM accounts where email = :email";
				$stmt = $conndb->prepare($sql);
				$stmt -> bindParam(":email", $email);

				if($stmt -> execute()){

					$user = $stmt -> fetch();

					$user_id = $user["user_id"];
					$encyptedpass = $user["password"];
					$salt = $user["salt"];
					
					//Addes each pepper one at a time to the password.
					for($x=0;$x<=26;$x++){

						$newpass = $pass.$salt.$x;

						//Checks if the password matches
						if(password_verify($newpass,$encyptedpass)){

							$running = "yes";

							//Loops is used to create a new session code incase the one created is already in use
							while($running=="yes"){
								$generatedCode = str_shuffle("abcdefghijklmnopqrstuvwxyz1234567890");
								$sql = "SELECT * FROM session where generatedCode = :generatedCode AND status='active'";
								$stmt = $conndb->prepare($sql);
								$stmt -> bindParam(":generatedCode", $generatedCode);

								$stmt -> execute();
								$totalgeneratedCode = $stmt -> rowCount();

								//If the code generated isn't in use then it is used
								if($totalgeneratedCode==0){
									$running="no"; 

									$sql = "INSERT INTO session(generatedCode, account_id, unix_timestamp, status)
									VALUES (:generatedCode, :account_id, :unix_timestamp, :status)";

									$stmt = $conndb->prepare($sql);
									
									//Checks if inserting the session into the database failed.
									if ($stmt->execute(['generatedCode'=>$generatedCode, 'account_id'=>$user_id, 'unix_timestamp'=>$unix_timestamp, 'status'=>'active'])){
										//Creates the session
										$array = array ($generatedCode,$user_id);
										$_SESSION['user'] =  $array;

										header("Location: ../online/profile.php");
										exit();
																
									}else{
										header("Location: ../index.php?error=systemerror&email=".$email);
										exit();
									}
								
								}
							}
						
						break;
							//If no pepper works then the password is invalid
						}else if($x>=26){
							header("Location: ../index.php?error=failedlogin&email=".$email);
							exit();
						}
					}
				}else{
					header("Location: ../index.php?error=failedlogin&email=".$email);
					exit();
				}
			}
		}
	}else{
		header("Location: ../index.php?error=failedlogin&email=".$email);
		exit();
	}
}
