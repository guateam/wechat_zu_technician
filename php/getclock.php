<?php

date_default_timezone_set('PRC');

function getclock($id)
{
    $service = get('service_order', 'job_number', $id);
    if ($service) 
	{
        $todayclock = 0;
        $clock = 0;
        foreach ($service as $value) 
		{
            $order = get('consumed_order', 'order_id', $value['order_id']);
            if ($order && ($order[0]['state'] == 4 || $order[0]['state']==5)) 
			{
					$today_begin = strtotime(date("Y-m-d 00:00:00",time()));
					$today_begin = $today_begin + 9*3600;
					
					$today_end = strtotime(date("Y-m-d 23:59:59",time()));
					$today_end = $today_end + 9*3600;
					
                    if ($order[0]['end_time'] <= $today_end && $order[0]['end_time'] >= $today_begin) //根据时间戳，计算今日钟数
					{
                        $todayclock++;
                    }
                    
					$Begin = strtotime(date('Y-m-01', strtotime(date("Y-m-d"))));
					$Begin = $Begin + 9 * 3600;
					
					$BeginDate = date('Y-m-01', strtotime(date("Y-m-d")));
					$End = strtotime( date('Y-m-d', strtotime("$BeginDate +1 month")) );
					$End = $End + 9 * 3600;
					
					//if ($order[0]['end_time'] <= strtotime(date('Y-m') . '-31 23:59:59') && $order[0]['end_time'] >= strtotime(date('Y-m') . '-01 00:00:00')) //根据时间戳，计算本月钟数 					
					if ($order[0]['end_time'] <= $End && $order[0]['end_time'] >= $Begin) //根据时间戳，计算本月钟数 
					{
                        $clock++;
                    }
            }
        }
        return ['status' => 1, 'clock' => $clock, 'todayclock' => $todayclock];
    } 
	else 
	{
        return ['status' => 0];
    }
}
?>