<?php
//Loads the each year for the course
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	include("conn.php");
	$currentCourseId = $_POST["currentCourseId"];
	$type = $_POST["type"];

	$sql = "SELECT years FROM courses WHERE course_ID = :id";
	$stmt = $conndb -> prepare($sql);

	if($stmt -> execute(["id"=>$currentCourseId])){
		$year = $stmt -> fetch();
		
		if($type=="setup"){
			echo "<option disabled selected value>-- select year --</option>";
		}else if($type=="search"){
			echo "<option value='any'>Any year</option>";
		}

		for($a = 1;$a <= $year["years"]; $a++){
		echo "<option value='".$a."'>".$a."</option>";
		}

	}
}
?>