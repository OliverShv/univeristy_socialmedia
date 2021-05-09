<?php
//Searchs for group ids relating to the groups name
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST["name"])) {
        require 'conn.php';

        $name = $_POST["name"];

        $sql = "SELECT group_id FROM groups WHERE name LIKE '%$name%'";
        $stmt = $conndb -> prepare($sql);
        $stmt ->execute();
        $array = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);

        echo json_encode($array);
    }
}
?>