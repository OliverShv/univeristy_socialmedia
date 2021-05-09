<?php
//Connection for the database
$servername = "localhost";
$username = "root";
$password = "";

try {
    $conndb = new PDO("mysql:host=$servername;dbname=dbs", $username, $password);
    // set the PDO error mode to exception
    $conndb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }

?>