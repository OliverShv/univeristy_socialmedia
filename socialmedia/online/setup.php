<?php require("../php/conn.php") ?>
<?php require("../php/verifylogin.php") ?>

<?php

if(isset($_POST["course"])){

    $course=$_POST["course"];
    $year=$_POST["year"];
    $modules=$_POST["modules"];

    //update course year
    $sql ="UPDATE accounts
            SET cyear = :year
            WHERE user_id = :user_id;";
    
    $stmt = $conndb -> prepare($sql);
    $stmt -> execute(["year"=>$year,"user_id"=>$details[1]]);

    //update course 
    $sql ="DELETE FROM studentcourse WHERE student_id=:user_id";
    $stmt = $conndb -> prepare($sql);
    $stmt -> execute(["user_id"=>$details[1]]);

    $sql = "INSERT INTO studentcourse(course_id,student_id)
    VALUES (:course,:user_id)";
    $stmt = $conndb -> prepare($sql);
    $stmt -> execute(["course"=>$course,"user_id"=>$details[1]]);

    //update modules
    $sql ="DELETE FROM studentmodule WHERE student_id=:user_id";
    $stmt = $conndb -> prepare($sql);
    $stmt -> execute(["user_id"=>$details[1]]);

    $sql = "INSERT INTO studentmodule(module_id,student_id)
    VALUES (:module,:user_id)";
    $stmt = $conndb -> prepare($sql);

    for($x=0;$x<count($modules);$x++){
        $stmt -> execute(["module"=>$modules[$x],"user_id"=>$details[1]]);
    };
    
    header("Location: profile.php");
    exit();
}

?>

<?php 

    //Get courses
    $sql = "SELECT course_id,cname,years FROM courses";
    $stmt = $conndb -> prepare($sql);

    if($stmt -> execute()){
        $course = $stmt -> fetchAll();
    }else{
        header("settings.php?error");
        exit();
    }

?>

<?php include("../php/header.php") ?>

<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script>
//Get course year after course is selected
$(document).ready(function(){
	$("#courses").change(function(){
        var modules = document.getElementById("modules").innerHTML="";
		var courseId = document.getElementById("courses").value;
		$("#courseyear").load("../php/fetchyears.php",{
			currentCourseId : courseId,
            type : "setup"
		});
	});
});
//Get course modules when year is selected
$(document).ready(function(){
	$("#courseyear").change(function(){
		var courseId = document.getElementById("courses").value;
		var year = document.getElementById("courseyear").value;
		$("#modules").load("../php/fetchmodules.php",{
			currentCourseId : courseId,
			currentYear : year,
            type: "setup"
		});
	});
});
</script>

    <link rel="stylesheet" type="text/css" href="../css/index.css">
    <title>Set up</title>
  </head>
  <body>
 
  <div class="d-flex justify-content-center align-items-center full">
        <div class="modules-design">
            <img src="../img/uow-logo.png" style="width:100%">
                
            <div class="py-4 px-4">

                <p style="font-size: 27px">Modules select</p>

                <form style="font-size: 20px" method="POST" action="setup.php">
                    <!-- Select Course -->
                    <div class="form-group">
                        <select class="button-design" name="course" id="courses" required>
                            <option disabled selected value>-- select an course --</option>
                            <?php
                                for($a = 0;$a < sizeof($course); $a++){
                                    echo "<option value='".$course[$a][0]."'>".$course[$a][1]."</option>";
                                };
                            ?>
                        </select>
                    </div>
                    <!-- Select course year -->                          
                    <div class="form-group">
                        <select class="button-design" name="year" id="courseyear" required>
                            <option disabled selected value>-- select an year --</option>
                        </select>
                    </div>
                    <!-- Select modules -->
                    <div class="form-group">
                        <table name="modules" id="modules" style="color:black;"></table>
                    </div>

                    <button type="submit" style="background-color:#3FAAF2; color:white" class="button-design button-interact">Submit</button>
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

                <button type="button" onclick="window.location.href='settings.php'" style="background-color:#DAF1FE; color:#707070; font-size: 20px" class="button-design button-interact">Cancel</button>
            </div>
        </div>
  </div>

  <?php include("../php/footer.php") ?>