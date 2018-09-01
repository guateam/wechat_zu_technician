<?php
require("database.php");

$job_number = $_POST['job_number'];
$change = ["photo",$_POST['url']];
set("technician","job_number",$job_number,[$change]);
?>