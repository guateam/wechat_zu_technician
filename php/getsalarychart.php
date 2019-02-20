<?php
function getsalarychart($id)
{
    $service = get('service_order', 'job_number', $id);
    $tech = get('technician','job_number',$id);
    $today_begin = strtotime(date("Y-m-d 00:00:00",time()));
    $today_end = strtotime(date("Y-m-d 23:59:59",time()));

    $time = [];
    $data = [];
    for($i=0;$i<8;$i++){
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
                $order = get('consumed_order', 'order_id', $value['order_id']);
                if ($order) 
				{


                    if (intval($order[0]['generated_time']) >= $begin && intval($order[0]['generated_time']) <= $end   ) 
					{
                        if ($order[0]['state'] == 4 || $order[0]['state'] == 5) 
						{
                            if ($value['service_type'] == 1) 
							{
                                $servicetype = get('service_type', 'ID', $value['item_id']);
                                if ($servicetype) 
								{
                                    //排钟
                                    if($value['clock_type'] == 1){
                                        //技师
                                        if($tech[0]['type'] == 1)
                                            $data[0] += $servicetype[0]['pai_commission'] / 100;
                                        //接待
                                        else
                                            $data[0] += $servicetype[0]['pai_commission2'] / 100;
                                    
                                    //点钟
                                    }else if($value['clock_type'] == 2){
                                        //技师
                                        if($tech[0]['type'] == 1)
                                            $data[0] += $servicetype[0]['commission'] / 100;
                                        //接待
                                        else
                                            $data[0] += $servicetype[0]['commission2'] / 100;
                                    }
                                   
                                }
                            }
                        }
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


function get_invite_bonus_2($job_number,$begin,$end){

    $invited = get("inviteship", 'inviter_job_number', $job_number);

    $total_earn = 0;

    if ($invited) {
        foreach ($invited as $inv) {
            //获取下家信息
            $fresh_jbnb = $inv['freshman_job_number'];
            //获取下家支付给本家的钱
            $yeji = get_lost_2($inv['freshman_job_number'], $begin, $end);

            $total_earn += $yeji['lost'];
        }
    }
    return $total_earn;
}




function get_lost_2($job_number, $begin, $end)
{
    //该技师自己做的所有订单
    
    $so = sql_str("select A.* from service_order A,consumed_order B where A.job_number='$job_number' and B.order_id = A.order_id and B.generated_time >=$begin and B.generated_time <= $end ");
    //邀请该技师的人
    $self_p = sql_str("select * from inviteship where `freshman_job_number`='$job_number'");
    //付给邀请自己的人的钱
    $lost = 0;

    if ($so) {
        foreach ($so as $idx => $svod) {
            $item_id = $svod['item_id'];
            $order_id = $svod['order_id'];
            $consumed = sql_str("select state,generated_time from consumed_order where order_id = '$order_id'");

            if(!$consumed || ( $consumed[0]['state'] != 4 && $consumed[0]['state'] != 5))continue;

            $item = sql_str("select * from service_type where `ID`='$item_id'");
            if ($item) {
                //计算支付给邀请人的钱
                if ($self_p && $item) {
                    $lost += $item[0]['invite_income'] / 100;
                }
                $so[$idx] = array_merge($so[$idx], ['lost' => $item[0]['invite_income'] / 100,'name'=>$item[0]['name']]);
            }else{
                $so[$idx] = array_merge($so[$idx], ['lost' => 0,'name'=>'该服务已被删除']);
            }
            $so[$idx]['appoint_time'] = $consumed[0]['generated_time'];
        }
    }

    return ['lost' => $lost, 'order' => $so];
}

?>