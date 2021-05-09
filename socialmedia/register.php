<?php
//If the user is already logged in then they are sent to their profile
session_start();

if(isset($_SESSION['user'])){
    header("Location: online/profile.php");
    exit();
}

?>
<?php require("php/conn.php") ?>
<?php require("php/registration.php") ?>
<?php include("php/header.php") ?>
<?php

//If a ID was sent with the register URL then it is inserted into the ID input
if(isset($_GET["id"])){
    $id = $_GET["id"];
} else {
    $id = "";
}
//If a email was sent with the register URL then it is inserted into the email input
if(isset($_GET["email"])){
    $email = $_GET["email"];
} else {
    $email = "";
}
//If a forename was sent with the register URL then it is inserted into the forename input
if(isset($_GET["forename"])){
    $forename = $_GET["forename"];
} else {
    $forename = "";
}
//If a surname was sent with the register URL then it is inserted into the surname input
if(isset($_GET["surname"])){
    $surname = $_GET["surname"];
} else {
    $surname = "";
}

//Error messages
if(isset($_GET["error"])){
    $error = $_GET["error"];
    
    if($error=="error01"){
        $message= "Some fields are missing";
    }
    if($error=="error02"){
        $message= "Email is in the incorrect format";
    }
    if($error=="error03"){
        $message= "Student Id is in the incorrect format";
    }
    if($error=="error04"){
        $message= "Forename is in the incorrect format";
    }
    if($error=="error05"){
        $message= "Surname is in the incorrect format";
    }
    if($error=="error06"){
        $message= "The passwords don't match";
    }
    if($error=="error07"){
        $message= "The password is in the incorrect format";
    }
    if($error=="error08"){
        $message= "The student ID has already been registered";
    }
    if($error=="error09"){
        $message= "The email has already been registered";
    }
    if($error=="error10"){
        $message= "System error, try again";
    }
}

?>
    <link rel="stylesheet" type="text/css" href="css/index.css">
        <title>Register</title>
    </head>
    <body>

    <div class="d-flex justify-content-center align-items-center full">
        <div class="register-design">
            <img src="img/uow-logo.png" style="max-height:170px;" class="image-center img-fluid">
            <!-- Shows error message if one is available -->
            <?php if(!empty($message)){ echo '<p style="font-size: 25px; font-weight:600;color:red;text-align:center;margin-bottom:0px">'.$message.'</p>';} ?>
            <form action="register.php" method="POST">   
                <div class="form-spacing">                
                    <div class="row row-margin">

                        <div class="col-sm-6 col-12">
                            <p style="font-size: 40px; font-weight:600">Registration</p>
                        </div>
                        <div class="col-sm-6 col-12">
                            <!-- Input requires the user to insert a ID consisting of 8 numbers -->
                            <input class="button-design pl-4" type="number" name="id" class="form-control" placeholder="Student number" min="10000000" max="99999999" oninvalid="setCustomValidity('Enter student number which consists of 8 numbers')" onchange="try{setCustomValidity('')}catch(e){}" value=<?php echo "'".$id."'"; ?> required>
                        </div>

                    </div>

                    <div class="row row-margin">
                        <!-- Input requires the user to insert in a university of worcester email -->
                        <div class="col-12">
                            <input class="button-design pl-4" type="email" name="email" class="form-control" placeholder="Student email" pattern="[a-z0-9_]+@uni.worc.ac.uk$" oninvalid="setCustomValidity('Univeristy emails end in @uni.worc.ac.uk')" onchange="try{setCustomValidity('')}catch(e){}" value=<?php echo "'".$email."'"; ?> required> 
                        </div>

                    </div>

                    <div class="row row-margin">

                        <div class="col-sm-6 col-12">
                            <!-- Input requires the user to insert a forename with the stated requirements -->
                            <input class="button-design pl-4 input-margin" type="text" name="forename" class="form-control" placeholder="Forename" pattern="^[A-Za-z ,.'-]{1,35}$" oninvalid="setCustomValidity('Forename can only contain Capital letters, non capital letters, spaces, commas, full stops, apostrophes or dashes. Name can only be up to 35 characters')" onchange="try{setCustomValidity('')}catch(e){}" value=<?php echo "'".$forename."'"; ?> required>
                        </div>
                        <div class="col-sm-6 col-12">
                            <!-- Input requires the user to insert a surname with the stated requirements -->
                            <input class="button-design pl-4" type="text" name="surname" class="form-control" placeholder="Surname" pattern="^[A-Za-z ,.'-]{1,35}$" oninvalid="setCustomValidity('Surname can only contain Capital letters, non capital letters, spaces, commas, full stops, apostrophes or dashes. Name can only be up to 35 characters')" onchange="try{setCustomValidity('')}catch(e){}" value=<?php echo "'".$surname."'"; ?> required>
                        </div>

                    </div>

                    <div class="row row-margin">

                        <div class="col-sm-6 col-12">
                            <!-- Input requires the user to insert a password that has a capital letter, lowercase letter and number with a minimum length of 8 characters or the form won't submit -->
                            <input class="button-design pl-4 input-margin" type="password" name="pass1" class="form-control" placeholder="Password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" oninvalid="setCustomValidity('Password must contain atleast one capital letter, non-capital letter, and number with a minimum length of 8 characters')"  onchange="try{setCustomValidity('')}catch(e){}" required>
                        </div>
                        <div class="col-sm-6 col-12">
                            <!-- Input requires the user to insert a password that has a capital letter, lowercase letter and number with a minimum length of 8 characters or the form won't submit -->
                            <input class="button-design pl-4" type="password" name="pass2" class="form-control" placeholder="Repeat password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" oninvalid="setCustomValidity('Password must contain atleast one capital letter, non-capital letter, and number with a minimum length of 8 characters')"  onchange="try{setCustomValidity('')}catch(e){}" required>
                        </div>

                    </div>
                    
                    <div class="row row-margin">
                        <div class="col-12" style="text-align:center">
                            <button type="submit" style="background-color:#3FAAF2; color:white; max-width:420px" class="button-design button-interact">Register</button>
                        </div>
                    </div>
            </form>
                </div>
        </div>
    </div> 

    <div class="container-fluid">
        <a href="about.php" >About</a>
        <a href="index.php">Login</a>
        <a href="register.php">Register</a>
    </div>

    <?php include("php/footer.php") ?>