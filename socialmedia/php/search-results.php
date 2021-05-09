<?php
//Page is used to search for users
if($_SERVER['REQUEST_METHOD'] === 'GET') {
    //Checks whether a specific course, year or module was requested
    if(isset($_GET["course"])){
        if($_GET["course"]!="any"){
            $course = $_GET["course"];
        }
    }else{
        $course = "";
    }

    if(isset($_GET["year"])){
        if($_GET["year"]!="any"){
            $year = $_GET["year"];
        }
    }else{
        $year = "";
    }

    if(isset($_GET["modules"])){
        $modules = $_GET["modules"];
    }else{
        $modules = "";
    }
    //If atleast one was then this section runs 
    if(!empty($course) || !empty($year) || !empty($modules)){

        //The sql looks for all users that are apart of a course
        $sql = "SELECT DISTINCT user_id, fname, lname, cyear, picture FROM studentcourse INNER JOIN accounts ON accounts.user_id = studentcourse.student_id INNER JOIN studentmodule ON accounts.user_id = studentmodule.student_id WHERE 1=1 ";

        //Depending on what was requested, different search parameters are added to the sql
        if(!empty($_GET["studentname"])){
            $name = $_GET["studentname"];
            $sql .= " AND CONCAT( fname, ' ', lname ) LIKE '%". $name . "%'";
        }

        if(!empty($_GET["course"])){
            $course = $_GET["course"];
            if($course!="any"){
                $sql .= " AND course_id = '". $course . "'";
            }
        }

        if(!empty($_GET["year"])){
            $year = $_GET["year"];
            if($year!="any"){
                $sql .= " AND cyear = ". $year;
            }
        }

        if(!empty($_GET["modules"])){
            $modules = $_GET["modules"];

            if(sizeof($modules)>1){

                $sql .= " AND (module_id = '". $modules[0] . "'";

                for($x=1;$x<sizeof($modules);$x++){
                    $sql .= " OR module_id = '". $modules[$x] . "'";
                }

                $sql .= ")";

            }else{
                
                $sql .= " AND module_id = '". $modules[0] . "'";

            }
        }

        //If no course informaiton was added then this runs
    }else{
        $sql = "SELECT DISTINCT user_id, fname, lname, cyear, picture FROM studentcourse INNER JOIN accounts WHERE 1=1";
        
        if(!empty($_GET["studentname"])){
            $name = $_GET["studentname"];
            $sql .= " AND CONCAT( fname,  ' ', lname ) LIKE '%". $name . "%'";
        }
    }

    //The users are ordered by last name
    $sql .= " ORDER BY lname ASC";

    $stmt = $conndb -> prepare($sql);

    //Resutls are collected
    if($stmt -> execute()){
        $user = $stmt ->fetchAll();
        $course = array();
        $modules = array();
        $indicator = array();

        if(isset($user[0]["user_id"])){
            $results = count($user);

            for($x=0; $x<$results; $x++){
            
                //Find student course
                $sql = "SELECT cname FROM studentcourse INNER JOIN courses ON studentcourse.course_id = courses.course_id WHERE student_id=". $user[$x]["user_id"];
                $stmt = $conndb -> prepare($sql);

                if($stmt -> execute()){
                    if($stmt -> rowCount()!=0){
                        $stmt -> execute();
                        $user[$x]["course"] = $stmt ->fetch()["cname"];
                    }else{
                        $user[$x]["course"] =  null;
                    }
                }

                //Users year is styled
                if($user[$x]["cyear"]==1){
                    $user[$x]["indicator"]="st";
                }else if($user[$x]["cyear"]==2){
                    $user[$x]["indicator"]="nd";
                }else if($user[$x]["cyear"]==3){
                    $user[$x]["indicator"]="rd";
                }else{
                    $user[$x]["indicator"]="th";
                }
            }
        }else{
            $results = 0;
        }
    }
}else{
    exit();
}
?>