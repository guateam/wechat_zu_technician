<?php
require('database.php');
$job_number = $_POST['job_number'];
$tips = get("tip",'technician_id',$job_number);
echo json_encode(['status'=>1,'data'=>$tips]);