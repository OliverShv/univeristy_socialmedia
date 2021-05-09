<?php require("../php/conn.php") ?>
<?php require("../php/verifylogin.php") ?>
<?php include("../php/header.php") ?>
<?php include("../php/search-results.php") ?>

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

<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script>
//Get course year after course is selected
$(document).ready(function(){
	$("#courses").change(function(){
        var modules = document.getElementById("modules").innerHTML="";
		var courseId = document.getElementById("courses").value;
		$("#courseyear").load("../php/fetchyears.php",{
			currentCourseId : courseId,
      type: "search"
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
      type: "search"
		});
	});
});
</script>

<link rel="stylesheet" type="text/css" href="../css/search.css">
<link rel="stylesheet" type="text/css" href="../css/navbar.css">
<script src = "../js/search.js"></script>
    <title>Search</title>
  </head>
  <body>
<?php include("../php/navbar.php") ?>

<div class="d-flex justify-content-center align-items-center full">
  <div id="design-area" class="settings-design">         
      <h1 style="text-align:center" id="searchButton" onclick="hideSearch()">Search</h1>
    <div class="row">
        <div id="searchOptions" class="col-12 col-sm-6 search-area">

          <form action="search.php" method="GET">

                    <div class="form-group">
                    <input class="button-design pl-4 input-margin form-control" placeholder="Student's name" type="text" name="studentname" pattern="^[A-Za-z ,.'-]{0,70}$" oninvalid="setCustomValidity('Student name can only contain Capital letters, non capital letters, spaces, commas, full stops, apostrophes or dashes. Name can only be up to 35 characters')" onchange="try{setCustomValidity('')}catch(e){}" value="<?php  if(isset($_GET["studentname"])){ echo $_GET["studentname"];} ?>">
                    </div>
                    <!-- Course options -->
                    <div class="form-group">
                        <select class="button-design" name="course" id="courses" style="padding-left:20px" required>
                            <option value="any">Any course</option>
                            <?php
                                for($a = 0;$a < count($course); $a++){
                                echo "<option value='".$course[$a]["course_id"]."'>".$course[$a]["cname"]."</option>";
                                };
                            ?>
                        </select>
                    </div>
                    <!-- year options -->            
                    <div class="form-group">
                        <select class="button-design" name="year" id="courseyear" style="padding-left:20px" required>
                            <option value="any">Any year</option>
                        </select>
                    </div>
                    <!-- Module options -->            
                    <div class="form-group">
                        <table name="modules" id="modules" style="color:black;"></table>
                    </div>

              <button type="submit" style="background-color:#3FAAF2; color:white;" class="button-design button-interact input-margin">Search</button>

          </form>

        </div>
        <div class="col-12 col-sm-6" style="overflow:auto;height:550px">
          <?php
          
          //Search results are shown here
          if($results!=0){
          
            for($x=0;$x<$results;$x++){

              include("../php/search-template.php");
              
            };

          }else{

            echo "No users found";

          }
          ?>                         
        </div>
      </div>
    </div>
</div>

<?php include("../php/footer.php") ?>