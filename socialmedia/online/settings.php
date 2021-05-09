<?php require("../php/conn.php") ?>
<?php require("../php/verifylogin.php") ?>
<?php include("../php/header.php") ?>
<?php include("../php/update-settings.php") ?>
<link rel="stylesheet" type="text/css" href="../css/settings.css">
<link rel="stylesheet" type="text/css" href="../css/navbar.css">

    <title>Settings</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="../js/settings.js"></script>

  </head>
  <body>

  
    <?php include("../php/navbar.php") ?>
    <?php
    //Checks if a update happened
    if(isset($_GET['update'])){
        if($_GET['update']=="successful"){
            $success="true";
        }else{
            $success="false";
        }
    }else{
        $success="false";
    }
    ?>

    <div class="d-flex justify-content-center align-items-center full">
        <div class="settings-design">
                
            <div class="py-4 px-4">

                <p style="font-size: 40px; font-weight:600; text-align:center">Settings</p>
                <!-- Shows error message or success message -->
                <p id="message-field" class="<?php if($success=="true"){echo 'success-field';}else{echo 'error-field';} ?>" style="font-size: 25px; font-weight:600;text-align:center;margin-bottom:10px">  <?php if($success=="true"){ echo 'Update successful';}else if(isset($error)){ echo $error;} ?></p>;

                <form style="font-size: 20px" onsubmit="return formChecker()" method="POST" autocomplete="off" action="settings.php" enctype="multipart/form-data">

                    <div class="row row-margin" style="border-bottom:1px solid #707070;padding-bottom:12px;">
                        <!-- Image preview -->
                        <div class="col-sm-3 col-12" id="img-div">
                            <div id="profileImage" class="input-margin" style="background-image: url('../img/profilepics/<?php echo $picture ?>');"></div>
                        </div>
                        
                        <!-- Image upload/delete options -->
                        <div class="col-sm-3 col-12">
                            <label class="button-design button-interact upload-button" for="upload">Change picture</label>
                            <input type="file" onclick="supportedTypes()" accept="image/*" onchange="picture(event)" id="upload" name="profilepic" >
                            <button type="button" style="background-color:#3FAAF2; color:white;" onclick="deleteImage()" class="button-design button-interact input-margin">Delete picture</button>
                            <input id="image-status" name="imageStatus" value="same" autocomplete="off" style="display:none">
                        </div>

                        <!-- Password change area -->
                        <div class="col-sm-6 col-12" id="pass-area">
                            <input class="button-design pl-4 form-control" style="margin-bottom:12px;" type="password" id="pass1" name="pass1" placeholder="New password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" oninvalid="setCustomValidity('Password must contain atleast one capital letter, non-capital letter, and number with a minimum length of 8 characters')"  onchange="try{setCustomValidity('')}catch(e){}">
                            <input class="button-design pl-4 form-control" type="password" id="pass2" name="pass2" placeholder="Confirm password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" oninvalid="setCustomValidity('Password must contain atleast one capital letter, non-capital letter, and number with a minimum length of 8 characters')"  onchange="try{setCustomValidity('')}catch(e){}">
                        </div>
                    
                    </div>

                    <!-- Forename and surname -->
                    <div class="row row-margin">

                        <div class="col-sm-6 col-12">
                            <input class="button-design pl-4 input-margin form-control" type="text" name="forename" placeholder="Forename" pattern="^[A-Za-z ,.'-]{1,35}$" oninvalid="setCustomValidity('Forename can only contain Capital letters, non capital letters, spaces, commas, full stops, apostrophes or dashes. Name can only be up to 35 characters')" onchange="try{setCustomValidity('')}catch(e){}" value="<?php echo htmlentities(ucfirst($fname)); ?>" required>
                        </div>
                        <div class="col-sm-6 col-12">
                            <input class="button-design pl-4 form-control" type="text" name="surname"  placeholder="Surname" pattern="^[A-Za-z ,.'-]{1,35}$" oninvalid="setCustomValidity('Surname can only contain Capital letters, non capital letters, spaces, commas, full stops, apostrophes or dashes. Name can only be up to 35 characters')" onchange="try{setCustomValidity('')}catch(e){}" value="<?php echo htmlentities(ucfirst($lname)); ?>" required>
                        </div>

                    </div>

                    <!-- Bio -->
                    <div class="row row-margin">

                        <div class="col-12">
                            <textarea onchange="textAreaError()" class="textarea-design pl-4 input-margin form-control" style="height:200px;" type="text" id="bio" name="bio" placeholder="About me"><?php echo $bio; ?></textarea>
                        </div>

                    </div>

                    <!-- Update -->
                    <div class="row row-margin">
                        <div class="col-12" style="text-align:center">
                            <button type="submit" style="background-color:#3FAAF2; color:white; max-width:420px" class="button-design button-interact">Update</button>
                        </div>
                    </div>
                </form>

                <div class="row mt-4">
                    <div class="col-5">
                        <hr>
                    </div>

                    <div class="col-2">
                    <p style="font-size: 27px;text-align:center">OR</p>
                    </div>

                    <div class="col-5">
                        <hr>
                    </div>
                </div>
                <!-- Change course info -->
                <div class="row row-margin">
                        <div class="col-12" style="text-align:center">
                            <a href="setup.php"><button type="button" style="background-color:#DAF1FE; color:#707070; max-width:420px" class="button-design button-interact">Edit course information</button></a>
                        </div>
                    </div>
            </div>
        </div>
  </div> 

<?php include("../php/footer.php") ?>