<?php
//If the user is already logged in then they are sent to their profile
session_start();

if(isset($_SESSION['user'])){
    header("Location: online/profile.php");
    exit();
}

?>
<?php include("php/header.php") ?>
<?php

//If a email was sent with the login URL then it is inserted into the email input
if(isset($_GET["email"])){
    $email = $_GET["email"];
} else {
    $email = "";
}

?>
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <title>Homepage</title>
    </head>
    <body>
 
    <div class="d-flex justify-content-center align-items-center full">
        <div class="login-design">
            <img src="img/uow-logo.png" style="width:100%">
                
            <div class="py-4 px-4">
            <p id="message-field" style="font-size: 25px; font-weight:600;text-align:center;margin-bottom:10px; color:darkred">  <?php if(isset($_GET["error"])){ echo "Login failed, try again";} ?></p>
                <p style="font-size: 27px">Sign in</p>

                <form style="font-size: 20px" method="POST" action="php/login.php">
                    <div class="form-group">
                        <!-- Input requires the user to insert in a university of worcester email or the form won't submit -->
                        <input class="button-design pl-4 form-control" type="email" name="email" placeholder="Student email" pattern="[a-z0-9_]+@uni.worc.ac.uk$" oninvalid="setCustomValidity('Univeristy emails end in @uni.worc.ac.uk')" onchange="try{setCustomValidity('')}catch(e){}" value=<?php echo "'".$email."'"; ?> required>
                    </div>

                    <div class="form-group">
                        <!-- Input requires the user to insert a password that has a capital letter, lowercase letter and number with a minimum length of 8 characters or the form won't submit -->
                        <input class="button-design pl-4 form-control" type="password" name="pass" placeholder="Password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" oninvalid="setCustomValidity('Password must contain atleast one capital letter, non-capital letter, and number with a minimum length of 8 characters')"  onchange="try{setCustomValidity('')}catch(e){}" required>
                    </div>

                    <button type="submit" style="background-color:#3FAAF2; color:white" class="button-design button-interact">Log in</button>
                </form>

                <div class="row mt-4">
                    <div class="col-5">
                        <hr>
                    </div>

                    <div class="col-2">
                    <p style="font-size: 27px">OR</p>
                    </div>

                    <div class="col-5">
                        <hr>
                    </div>
                </div>
                <!-- Sends user to the registration page -->
                <button type="button" onclick="window.location.href='register.php'" style="background-color:#DAF1FE; color:#707070; font-size: 20px" class="button-design button-interact">Register</button>

            </div>
        </div>
    </div>  
  
    <div class="container-fluid">
        <a href="about.php">About</a>
        <a href="index.php">Login</a>
        <a href="register.php">Register</a>
    </div>

    <?php include("php/footer.php") ?>