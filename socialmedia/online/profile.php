<?php require("../php/conn.php") ?>
<?php require("../php/verifylogin.php") ?>
<?php include("../php/header.php") ?>
<?php include("../php/profile-info.php") ?>
<link rel="stylesheet" type="text/css" href="../css/profile.css">
<link rel="stylesheet" type="text/css" href="../css/navbar.css">

    <title>Profile</title>
  </head>
  <body>

  <?php include("../php/navbar.php") ?>

<div class="d-flex justify-content-center">
        <!-- Profile area -->
            <div id="profileArea">

                <div class="row" id="profileInfo">

                    <div id="profileImage" style="background-image: url('../img/profilepics/<?php echo $picture ?>');"></div>
                    
                    <div id="profileNameContainer">
                        <h3 id="profileName"><?php echo $fullname." "; ?><?php if(isset($profile_id)){echo $messageoption;}  ?></h3>
                    </div>
                    
                </div>

                <div class="row" id="profileSection2">
                    
                    <!-- About me -->
                    <div class="col-md-5 col-12 borderPreset" id="profileAboutMe">
                        <div class="row profileHeadings">
                            <p>About me</p>
                        </div>
                        <div class="row">
                            <p><?php echo $bio; ?></p>
                        </div>
                    </div>
                    
                    <!-- Empty area -->
                    <div class="col-md-2 col-12" id="profileEmptyArea"></div>

                    <div class="col-md-5 col-12" id="profileSection3">
                        
                        <!-- Course -->
                        <div class="row borderPreset" id="profileCourse">
                            <div class="col">
                                <div class="row profileHeadings">
                                    <p>Course</p>
                                </div>
                                <div class="row">
                                    <p><?php 
                                        if(isset($cname)&&isset($cyear)){
                                            echo $cname. ", year ".$cyear;
                                        }else{
                                            echo "Course and year selection not completed";
                                        }

                                    ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modules -->
                        <div class="row borderPreset" id="profileModules">
                            <div class="col">
                                <div class="row profileHeadings">
                                    <p>Modules</p>
                                </div>
                                <div class="row">
                                    <p>
                                        <?php

                                            if(isset($modules[0])){
                                            for($a = 0;$a < count($modules); $a++){
                                                $b=$a+1;
                                                echo $b.". ".$modules[$a][0]."<br>";
                                                }
                                            }else{
                                                echo "No modules selected";
                                            }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div> 

                    </div>

                </div>

            </div>
        </div>

  <?php include("../php/footer.php") ?>