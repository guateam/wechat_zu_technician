<?php
require("database.php");

$inviter_job_number = $_POST['job_number'];
$begin = $_POST['begin'];
$end = $_POST['end'];


$newcome = get("inviteship",'inviter_job_number',$inviter_job_number);
$count = 0;
$bonus = 0;
if($newcome)
{
    foreach($newcome as $new)
	{
        $count++;
        $nb = $new['freshman_job_number'];

        $service_order = get("service_order","job_number",$nb);
        if(!$service_order)$service_order=[];
        $consumed_order = [];
        $service_type = get("service_type");
        if(!$service_type)$service_type = [];
        $last_soid = "";
        $key_info = [];
        //提成
        $ticheng = 0;
        //业绩
        $yeji = 0;
        foreach($service_order as $so)
		{
            $one_consumed_order = get("consumed_order","order_id",$so['order_id']);
            $time = $one_consumed_order[0]['generated_time'];
            $time = substr($time,0,10);
            if($so['service_type'] < 3 && ($time >= $begin && $time <= $end) )
			{
                foreach($service_type as $tp)
				{
                    if($tp['ID']== $so['item_id'])
					{
                        $so['price']=$tp['price']*$tp['discount']/100.0;
                        $so['price']/=100;
                        $ticheng+=$tp['commission']/100;
                        $yeji+=$so['price'];
                    }
                }
            }
        }
        ////////////////////////////
        $bonus+=($ticheng + $yeji)*$new['persentage']/100;
    }
}

echo json_encode([
    "count"=>$count,
    "bonus"=>$bonus
]);
?>