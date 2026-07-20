<?php
session_start();
$host = 'sql101.infinityfree.com';
$user = 'if0_42455233';
$pass = 'iJz8so2hWVQNAe';
$dbname = 'if0_42455233_car_workshop';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
