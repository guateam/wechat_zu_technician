<?php
require('database.php');
$job_number = $_POST['job_number'];
$invited = get("inviteship",'inviter_job_number',$job_number);
$tech = [];
if($invited){
    foreach($invited as $inv){

        $ods = get("service_order",'job_number',$inv['freshman_job_number']);
        $yeji = get_yeji($inv['freshman_job_number'],$ods);
        foreach($ods as $idx => $od){
            $name = get("service_type","ID",$od['item_id']);
            $tm = get('consumed_order','order_id',$od['order_id']);
            if($tm){
                $ods[$idx] = array_merge($ods[$idx],['time'=>$tm[0]['generated_time']]);
            }
            if($name){
                $ods[$idx]['item_id'] = $name[0]['name'];
                $ods[$idx]=array_merge($ods[$idx],['earn'=>$name[0]['commission']/100]);
            }
            $ods[$idx] = array_merge($ods[$idx],['show'=>true]);

        }

        array_push($tech,['job_number'=>$inv['freshman_job_number'],'order'=>$ods,'per'=>$inv['persentage']]);
    }
}
echo json_encode(['status'=>1,'data'=>$tech]);

function get_yeji($job_number,$so){
    //so是这个job_number技师的service_order

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