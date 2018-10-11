<?php
require("database.php");
function get_yeji($job_number){
    $so = get("service_order","job_number",$job_number);
    $invited = get("inviteship",'inviter_job_number',$job_number);
    $self = get("inviteship",'freshman_job_number',$job_number);
    $price = 0;
    $come_frome_other = 0;
    $other_data = [];
    $lost = 0;
    if($so){
        foreach($so as $svod){
            $item=get("service_type","ID",$svod['item_id']);
            $per = 0;
            if($self)$per =  $self[0]['persentage']/100;
            $lost+= ($item[0]['commission']/100)*$per;
            $price+=$item[0]['commission']/100;
        }
    }
    if($invited){
        foreach($invited as $inv){
            $data =get_yeji($inv['freshman_job_number']);
            $come_frome_other += $data['lost'];
            array_push($other_data,$data);
        }
    }
    return [
        'job_number'=>$job_number,
        'clock_num'=>count($so),
        'status'=>1,
        'earn'=>$price,
        'come_from_other'=>$come_frome_other,
        'lost'=>$lost,
        'final_salary'=>$price+$come_frome_other-$lost,
        'technicians'=>$other_data,
        ];
}
function get_all_yeji(){
    $techs = get("technician");
    $result = [];
    foreach($techs as $tech){
        array_push($result,get_yeji($tech['job_number']));
    }
    return $result;
}
if(!isset($_POST['job_number'])){
    echo  json_encode(['status'=>1,'data'=>get_all_yeji()]);
}else{
    $job_numbers = $_POST['job_number'];
    $result = [];
    foreach($job_numbers as $job_number){
        array_push($result,get_yeji($job_number));
    }
    echo json_encode(['status'=>1,'data'=>$result]);
}
