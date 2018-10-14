<?php
require("database.php");
$ids = $_POST['ids'];
$job_number = $_POST['job_number'];
$content = $_POST['content'];
add("friend_circle",[['content',$content],['img',$ids],['job_number',$job_number]]);
echo json_encode(['status'=>1]);