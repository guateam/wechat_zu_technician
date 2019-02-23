<?php
require("database.php");
$job_number = $_POST['job_number'];
$description = $_POST['description'];

$result = sql_str("update technician set description = '$description' where job_number='$job_number'");
echo json_encode(['status'=>1]);
?>