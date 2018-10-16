<?php
require('database.php');
$job_number = $_POST['job_number'];
$tips = get("tip",'technician_id',$job_number);
for($i=0;$i<count($tips);$i++){
    $tips[$i] = array_merge($tips[$i],['show'=>true]);
}
echo json_encode(['status'=>1,'data'=>$tips]);