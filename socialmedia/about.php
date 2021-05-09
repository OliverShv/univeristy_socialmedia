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
                <p style="font-size: 27px">About</p>               
                <p>This project was created for the university of worcester with the purpose of exploring the use of social media as a tool for univeristy students to interact with one another.</p>
            </div>
        </div>
    </div>  
  
    <div class="container-fluid">
        <a href="about.php">About</a>
        <a href="index.php">Login</a>
        <a href="register.php">Register</a>
    </div>

    <?php include("php/footer.php") ?>