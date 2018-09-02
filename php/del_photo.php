<?php
require("database.php");

$job_number = $_POST['job_number'];
$dir = $_POST['url'];

del("technician_photo","img",$dir);