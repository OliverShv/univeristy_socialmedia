<?php
//Page is used to collect students modules and courses as well as information of the last messages sent to the chats to be displayed
//get student course
$sql = "SELECT course_id FROM studentcourse WHERE student_id = :user_id";
$stmt = $conndb -> prepare($sql);

$stmt -> bindParam(":user_id", $details[1]);
$stmt -> execute();

$course_array = $stmt ->fetchAll(PDO::FETCH_COLUMN, 0);


//get student modules
$sql = "SELECT module_id FROM studentmodule WHERE student_id = :user_id";
$stmt = $conndb -> prepare($sql);

$stmt -> bindParam(":user_id", $details[1]);
$stmt -> execute();

$module_array = $stmt ->fetchAll(PDO::FETCH_COLUMN, 0);

//Merge the two arrays with the course being course
$cm_id = array_merge($course_array,$module_array);

if(count($cm_id)!=0){
    //Get course or module name
    for($x=0;$x<count($cm_id);$x++){
        if(strlen($cm_id[$x])==4){

            $cm_type[$x] = 'course';

            $sql = "SELECT cname FROM courses WHERE course_id = :course_id";
            $stmt = $conndb -> prepare($sql);
            $stmt -> bindParam(":course_id", $cm_id[$x]);
            $stmt -> execute();
            $cm_name[$x] = $stmt ->fetch()["cname"];

        }else if(strlen($cm_id[$x])==8){

            $cm_type[$x] = 'module';

            $sql = "SELECT mname FROM modules WHERE module_id = :module_id";
            $stmt = $conndb -> prepare($sql);
            $stmt -> bindParam(":module_id", $cm_id[$x]);
            $stmt -> execute();
            $cm_name[$x] = $stmt ->fetch()["mname"];

        }

        //Get total users in the chat
        $sql = "SELECT DISTINCT account_id FROM connections INNER JOIN session ON connections.session_id = session.session_id WHERE chat_id= :chat_id AND connections.status = 'active'";
        $stmt = $conndb -> prepare($sql);
        $stmt -> bindParam(":chat_id", $cm_id[$x]);
        $stmt -> execute();
        $cm_inchat[$x] = $stmt ->rowCount();
    }
}
?>