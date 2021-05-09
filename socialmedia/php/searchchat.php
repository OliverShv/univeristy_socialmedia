<?php
//Searchs for user ids relating to the users full name
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST["name"])) {
        require 'conn.php';

        $name = $_POST["name"];

        $sql = "SELECT user_id FROM accounts WHERE CONCAT( fname,  ' ', lname ) LIKE '%$name%'";
        $stmt = $conndb -> prepare($sql);
        $stmt ->execute();
        $array = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);

        echo json_encode($array);
    }
}
?>