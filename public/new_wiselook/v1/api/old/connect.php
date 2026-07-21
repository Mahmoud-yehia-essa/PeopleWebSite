<?php
$host = "82.29.180.63";
$port = "5011";
$dbname = "wise_db";
$username = "wise";
$password = "tag.amc.2013";

$conn = mysqli_connect($host, $username, $password, $dbname, $port);

// Check connection
if (!$conn) {
    die("Could not connect: " . mysqli_connect_error());
}

$conn->set_charset("utf8");
