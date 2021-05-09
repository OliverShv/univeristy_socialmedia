<?php
//Page is used to fetch modules
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	require("conn.php");
	//Loads modules that are related ot the course and year
	if($_POST["currentYear"]!=""){
		$currentCourseId = $_POST["currentCourseId"];
		$currentYear = $_POST["currentYear"];
		$type = $_POST["type"];

		$sql = "SELECT modules.mname,modules.module_id,modulecourse.mandatory FROM modules INNER JOIN modulecourse WHERE modules.module_id = modulecourse.module_id AND modulecourse.course_year = :year AND modulecourse.course_id = :id";
		$stmt = $conndb -> prepare($sql);
		if($stmt ->execute(["year"=>$currentYear,"id"=>$currentCourseId])){
			$modules = $stmt -> fetchAll();

			for($a = 0;$a < count($modules); $a++){
				$b = $a+1;

				//Option is loaded in two formats depending on if it is mandatory or not
				if($modules[$a][2]=="TRUE"&&$type=="setup"){
					echo "<tr><td>". $b.". " .$modules[$a][0]." (Mandatory)</td><td><input name='modules[]' type='checkbox' checked onclick='return false' value='".$modules[$a][1]."'></td></tr>";
				}else{
					echo "<tr><td>". $b.". " .$modules[$a][0]."</td><td><input name='modules[]' type='checkbox' value='".$modules[$a][1]."'></td></tr>";
				}

			};
		};
	};
};
?>
