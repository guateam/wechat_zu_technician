<?php
require('database.php');
date_default_timezone_set('PRC'); 

$job_number = $_POST['job_number'];
$begin = null;
$end = null;
if(isset($_POST['begin']))$begin = $_POST['begin'];
if(isset($_POST['end']))$end = $_POST['end'];
$begin = strtotime($begin);
$end = strtotime($end);
$invited = get("inviteship",'inviter_job_number',$job_number);
$tech = [];
$total_earn = 0;
if($invited)
{
    foreach($invited as $inv)
	{   
        //获取下家信息
        $fresh_jbnb = $inv['freshman_job_number'];
    //    $ods = get("service_order",'job_number',);

        //获取下家支付给本家的钱
        $yeji = get_lost($inv['freshman_job_number'],$begin,$end);
        // foreach($ods as $idx => $od)
		// {
        //     $tm = get('consumed_order','order_id',$od['order_id']);
        //     $ntime = strtotime($tm[0]['generated_time']);
        //     if($begin<$ntime && $ntime < $end)
		// 	{
        //         $name = get("service_type","ID",$od['item_id']);
        //         if($tm)
		// 		{
        //             $ods[$idx] = array_merge($ods[$idx],['time'=>$tm[0]['generated_time']]);
        //         }
        //         if($name)
		// 		{
        //             $ods[$idx]['item_id'] = $name[0]['name'];
        //             $ods[$idx]=array_merge($ods[$idx],['earn'=>$inv['persentage']*$name[0]['commission']/10000]);
        //             $earn+=$inv['persentage']*$name[0]['commission']/10000;
        //         }
        //         $ods[$idx]=array_merge($ods[$idx],['show'=>true]);
        //         array_push($show_ods,$ods[$idx]);
        //     }
        // }
        $total_earn+=$yeji['lost'];
        array_push($tech,['job_number'=>$inv['freshman_job_number'],'order'=>$yeji['order'],'lost'=>$yeji['lost']]);
    }
}
echo json_encode(['status'=>1,'data'=>$tech,'total_earn'=>$total_earn]);

function get_yeji($job_number,$so,$begin,$end)
{
    //so是这个job_number技师的service_order
    $invited = get("inviteship",'inviter_job_number',$job_number);

    //是否有上家
    $self = get("inviteship",'freshman_job_number',$job_number);

    //营业收入
    $price = 0;
    //来自下家收入
    $come_frome_other = 0;

    $other_data = [];
    //支付给上家
    $lost = 0;
    if($so)
	{
        foreach($so as $svod)
		{
            $item=get("service_type","ID",$svod['item_id']);
            if($self){
                $lost+= $item[0]['invite_income']/100;
            }

            $price+=$item[0]['commission']/100;
        }
    }
    if($invited)
	{
        foreach($invited as $inv)
		{
            $data =get_lost($inv['freshman_job_number'],$begin,$end);
            $come_frome_other += $data['lost'];
            array_push($other_data,$data['order']);
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

function get_lost($job_number,$begin,$end){
    //该技师自己做的所有订单
    $so = sql_str("select * from service_order where `job_number`='$job_number' and `appoint_time`>=$begin and `appoint_time` <= $end ");
    //邀请该技师的人
    $self_p =  sql_str("select * from inviteship where `freshman_job_number`='$job_number'");
    //付给邀请自己的人的钱
    $lost = 0;

    if($so){
        foreach($so as $idx => $svod){
            $item_id = $svod['item_id'];
            $item=sql_str("select * from service_type where `ID`='$item_id'");
            //计算支付给邀请人的钱
            if($self_p){
                $lost+= $item[0]['invite_income']/100;
            }
            $so[$idx] = array_merge($so[$idx],['lost'=>$item[0]['invite_income']/100]);
        }
    }

    return ['lost'=>$lost,'order'=>$so];
}