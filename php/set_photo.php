<?php
require("database.php");



$job_number = $_POST['job_number'];
$dir = $_POST['url'];
$change = ["photo",$dir];
set("technician","job_number",$job_number,[$change]);

?>