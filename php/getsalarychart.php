<?php

date_default_timezone_set('PRC');

function getsalarychart($id)
{
    //$service = get('service_order', 'job_number', $id);
    $tech = get('technician','job_number',$id);
	
	if ($tech && count($tech) > 0) 
	{
		$type = (int)$tech[0]['type'];
	}
	
    $today_begin = strtotime(date("Y-m-d 00:00:00",time()));
    $today_end = strtotime(date("Y-m-d 23:59:59",time()));
	
	$today_begin = $today_begin + 9*3600;
	$today_end = $today_end + 9*3600;

    $time = [];
    $data = [];
    for($i=0;$i<8;$i++)
	{
        array_unshift($time, date('m-d', (time() - ($i * 24 * 60 * 60))));
    }

    if ($tech) 
	{
        for ($i = 0; $i < 8; $i++) //每一天计算一次
		{
            $begin = $today_begin - ($i * 24 * 60 * 60);//往前推8天
            $end = $today_end - ($i * 24 * 60 * 60);
            array_unshift($data, 0);
            
			//$data[0]+= get_invite_bonus_2($id,$begin,$end);//提成不计算佣金
			
            if ($type == 1)
            {
                $service = sql_str("select * from service_order where job_number='$id' and order_id in (select order_id from consumed_order where end_time>=$begin and end_time <=$end and (state=4 or state=5) ) ");
                foreach ($service as $value) 
                {
                    $data[0] += $value['ticheng']/100;
                }
            }
            else if ($type == 2)
            {
                $service = sql_str("select * from service_order where jd_number='$id' and order_id in (select order_id from consumed_order where end_time>=$begin and end_time <=$end and (state=4 or state=5) ) ");
                foreach ($service as $value) 
                {
                    $data[0] += $value['jd_ticheng']/100;
                }

                //接待，还要计算上 充卡提成
                $recharge_bonus = get_recharge_bonus($id,$begin,$end,$type);

                $data[0] += $recharge_bonus;
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


?>