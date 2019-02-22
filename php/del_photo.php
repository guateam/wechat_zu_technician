<?php
require("database.php");

$job_number = $_POST['job_number'];
$dirs = $_POST['url'];

foreach($dirs as $dir)
{
   unlink($_SERVER['DOCUMENT_ROOT'].$dir); 
   del("technician_photo","img",$dir);
}
?>
