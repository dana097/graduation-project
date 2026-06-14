<?php

$servername = "sql112.infinityfree.com";
$username = "if0_41866050";
$password = "YOUR_PASSWORD"; // حطي كلمة المرور حقك هنا
$dbname = "if0_41866050_academic_organizer";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>

 