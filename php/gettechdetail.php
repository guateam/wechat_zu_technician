<?php
require("database.php");
$job_number = $_POST['job_number'];
$tech = get("technician","job_number",$job_number);
if($tech){
    echo json_encode([
        'status'=>1,
        'data'=>$tech
    ]);
}else{
    echo josn_encode([
        'status'=>0,
        'data'=>[]
    ]);
}
?>