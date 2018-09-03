<?php
require("database.php");

$job_number = $_POST['job_number'];
$dir = $_SERVER['DOCUMENT_ROOT'].$_POST['url'];

del("technician_photo","img",$_POST['url']);
unlink($dir);