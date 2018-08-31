<?php
require "database.php";
$phone = $_POST['phone'];
$tech = get("technician","phone_number",$phone);
echo json_encode([
    "job_number"=>$tech[0]['job_number']
]);
?>