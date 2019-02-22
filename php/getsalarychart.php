<?php

date_default_timezone_set('PRC');

function getsalarychart($id)
{
    $service = get('service_order', 'job_number', $id);
    $tech = get('technician','job_number',$id);
	
    $today_begin = strtotime(date("Y-m-d 00:00:00",time()));
    $today_end = strtotime(date("Y-m-d 23:59:59",time()));

    $time = [];
    $data = [];
    for($i=0;$i<8;$i++)
	{
        array_unshift($time, date('m-d', (time() - ($i * 24 * 60 * 60))));
    }

    if ($tech) 
	{
        for ($i = 0; $i < 8; $i++) 
		{
            $begin = $today_begin - ($i * 24 * 60 * 60);
            $end = $today_end - ($i * 24 * 60 * 60);
            array_unshift($data, 0);
            $data[0]+= get_invite_bonus_2($id,$begin,$end);
            foreach ($service as $value) 
			{
                $order_id=$value['order_id'];
                $order = sql_str("select * from consumed_order where order_id='$order_id' and end_time>=$begin and end_time <=$end and (state=4 or state=5) ");//get('consumed_order', 'order_id', $value['order_id']);
                if ($order) 
				{
                    $servicetype = get('service_type', 'ID', $value['item_id']);
                    if ($servicetype) 
					{
                        $data[0] += $value['ticheng']/100;
                    }
                }
                
            }
        }
        return ['status' => 1, 'data' => [
            'series' => [[
                'data' => $data,
            ]],
            'xAxis' => [
                'data' => $time,
            ],
        ]];
    }
	else
	{

        return ['status' => 1, 'data' => [
            'series' => [[
                'data' => [0,0,0,0,0,0,0,0],
            ]],
            'xAxis' => [
                'data' => [$time],
            ],
        ]];
    }
}


function get_invite_bonus_2($job_number,$begin,$end)
{

    $invited = get("inviteship", 'inviter_job_number', $job_number);

    $total_earn = 0;

    if ($invited) 
	{
        foreach ($invited as $inv) 
		{
            //获取下家信息
            $fresh_jbnb = $inv['freshman_job_number'];
            //获取下家支付给本家的钱
            $yeji = get_lost_2($inv['freshman_job_number'], $begin, $end);

            $total_earn += $yeji['lost'] / 100;
        }
    }
    return $total_earn;
}

function get_lost_2($job_number, $begin, $end)
{
    //该技师自己做的所有订单
    $so = sql_str("select A.* from service_order A,consumed_order B where A.job_number='$job_number' and B.order_id = A.order_id and B.end_time >=$begin and B.end_time <= $end and (B.state=4 or B.state=5)");
    //邀请该技师的人
    $self_p = sql_str("select * from inviteship where `freshman_job_number`='$job_number'");
    //付给邀请自己的人的钱
    $lost = 0;

    if ($so) {
        foreach ($so as $idx => $svod) {
            $item_id = $svod['item_id'];
            $order_id = $svod['order_id'];
            $consumed = sql_str("select state,end_time from consumed_order where order_id = '$order_id'");

            $item = sql_str("select * from service_type where `ID`='$item_id'");
            if ($item) {
                //计算支付给邀请人的钱
                if ($self_p && $item) {
                    $lost += $svod['yongjin'];
                }
                $so[$idx] = array_merge($so[$idx], ['lost' => $svod['yongjin'] / 100,'name'=>$item[0]['name']]);
            }else{
                $so[$idx] = array_merge($so[$idx], ['lost' => 0,'name'=>'该服务已被删除']);
            }
            $so[$idx]['appoint_time'] = $consumed[0]['end_time'];
        }
    }

    return ['lost' => $lost, 'order' => $so];
}

?>