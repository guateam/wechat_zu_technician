<?php
require('database.php');
$job_number = $_POST['job_number'];
$tips = get("tip",'technician_id',$job_number);
$result = [];
$begin = null;
$end = null;
if(isset($_POST['begin']))$begin = $_POST['begin'];
if(isset($_POST['end']))$end = $_POST['end'];
if(!is_null($begin) && !is_null($end)){
    for($i=0;$i<count($tips);$i++){
        $begin = strtotime($begin);
        $end = strtotime($end);
        $ntime = strtotime($tips[$i]['date']);
        if($begin<$ntime && $ntime < $end){
            $tips[$i] = array_merge($tips[$i],['show'=>true]);
            array_push($result,$tips[$i]);
        }
    }
}else{
    for($i=0;$i<count($tips);$i++){
        $tips[$i] = array_merge($tips[$i],['show'=>true]);
        array_push($result,$tips[$i]);
    }
}

echo json_encode(['status'=>1,'data'=>$result]);