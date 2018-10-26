<?php
require("database.php");
$ids = $_POST['ids'];
$video = $_POST['video'];
$job_number = $_POST['job_number'];
$content = $_POST['content'];
add("friend_circle",[['content',$content],['img',$ids],['video',$video],['job_number',$job_number],['date',time()]]);
echo json_encode(['status'=>1]);