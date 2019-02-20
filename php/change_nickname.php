<?php
require("database.php");
$job_number = $_POST['job_number'];
$nickname = $_POST['nickname'];

$result = sql_str("update technician set nickname = '$nickname' where job_number='$job_number'");
echo json_encode(['status'=>1]);