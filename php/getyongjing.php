<?php
require 'database.php';
date_default_timezone_set('PRC');

$job_number = $_POST['job_number'];
$begin = null;
$end = null;
if (isset($_POST['begin'])) 
{
    $begin = $_POST['begin'];
}

if (isset($_POST['end'])) 
{
    $end = $_POST['end'];
}

$begin = strtotime($begin);
$end = strtotime($end);
$invited = get("inviteship", 'inviter_job_number', $job_number);
$tech = [];
$total_earn = 0;
if ($invited) 
{
    foreach ($invited as $inv) 
	{
        //获取下家信息
        $fresh_jbnb = $inv['freshman_job_number'];
        //获取下家支付给本家的钱
        $yeji = get_lost($inv['freshman_job_number'], $begin, $end);
        $total_earn += $yeji['lost'];
        array_push($tech, ['job_number' => $inv['freshman_job_number'], 'order' => $yeji['order'], 'lost' => $yeji['lost']]);
    }
}
echo json_encode(['status' => 1, 'data' => $tech, 'total_earn' => $total_earn]);

function get_yeji($job_number, $so, $begin, $end)
{
    //so是这个job_number技师的service_order
    $invited = get("inviteship", 'inviter_job_number', $job_number);
    //营业收入
    $price = 0;
    //来自下家收入
    $come_frome_other = 0;

    $other_data = [];
    //店铺应支付给上家
    $lost = 0;

    foreach ($so as $svod) 
	{
        $lost += $svod['yongjin'] / 100;
        $price += $svod['ticheng'] / 100;
    }
    if ($invited) 
	{
        foreach ($invited as $inv) {
            $data = get_lost($inv['freshman_job_number'], $begin, $end);
            $come_frome_other += $data['lost'];
            array_push($other_data, $data['order']);
        }
    }
    return [
        'job_number' => $job_number,
        'clock_num' => count($so),
        'status' => 1,
        'earn' => $price,
        'come_from_other' => $come_frome_other,
        'lost' => $lost,
        'final_salary' => $price + $come_frome_other,
        'technicians' => $other_data,
    ];
}

function get_lost($job_number, $begin, $end)
{
    //该技师自己做的所有订单
    $so = sql_str("select A.*,B.end_time from service_order A,consumed_order B where A.job_number='$job_number' and B.order_id = A.order_id and B.end_time >=$begin and B.end_time <= $end and (B.state=4 or B.state=5)");
    //付给邀请自己的人的钱
    $lost = 0;

    if ($so) 
	{
        foreach ($so as $idx => $svod) 
		{
            $item_id = $svod['item_id'];

            $servicename = get("service_type", 'ID', $item_id);
            $svname = $servicename[0]['name'];

            $room = get("private_room", 'ID', $svod['private_room_number']);
            $roomname = $room[0]['name'];

            // $order_id = $svod['order_id'];
            //$consumed = sql_str("select state,end_time from consumed_order where order_id = '$order_id'");
            
            $lost += $svod['yongjin'] / 100;
            $so[$idx] = array_merge($so[$idx], ['lost' => $svod['yongjin'] / 100,'name'=>$svname,'roomname'=>$roomname]);
            $so[$idx]['appoint_time'] = $so[$idx]['end_time'];
        }
    }

    return ['lost' => $lost, 'order' => $so];
}
?>
