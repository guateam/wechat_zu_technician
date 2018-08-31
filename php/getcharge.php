<?php
require("database.php");

$job_number =$_POST['job_number'];
$begin = $_POST['begin'];
$end = $_POST['end'];

$money = 0;
$count = 0;

$charge = get("recharge_record","job_number",$job_number);
if($charge){
    foreach($charge as $ch){
        $time = $ch['generated_time'];
        $time = substr($time,0,10);
        if($time>=$begin && $time<=$end ){
            $money+=$ch['charge']/100;
            $count++;
        }
    }
    echo json_encode([
        'status'=>1,
        'count'=>$count,
        'charge'=>$money
    ]);
}else{
    echo json_encode([
        'status'=>0,
        'count'=>0,
        'charge'=>0
    ]);
}


?>