<?php

function getsalary($id)
{
	//获取技师类型
    $type = sql_str("select type from technician where job_number='$id'");
    if ($type) 
	{
        $type = (int)$type[0]['type'];//1为技师，2为接待
    }
	
	$day_begin = strtotime(date('Y-m-d') . ' 00:00:00');
	$day_end = strtotime(date('Y-m-d') . ' 23:59:59');
	
	$day_begin = $day_begin + 9*3600;
	$day_end = $day_end + 9*3600;
	
	$month_begin = strtotime(date('Y-m') . '-01 00:00:00');
	$month_end = strtotime(date('Y-m-t') . ' 23:59:59');
	
	$month_begin = $month_begin + 9*3600;
	$month_end = $month_end + 9*3600;
	
	if ($type === 1)
	{	
		$service = sql_str("select A.* from service_order A,consumed_order B where A.job_number = '$id' and A.order_id=B.order_id and (B.state=4 or B.state=5)");//处于待评价或者评价完成的服务
	}
	else if ($type === 2)
	{	
		$service = sql_str("select A.* from service_order A,consumed_order B where A.jd_number = '$id' and A.order_id=B.order_id and (B.state=4 or B.state=5)");//处于待评价或者评价完成的服务
	} 
	
    $todaysalary = 0;
    $salary = 0;
    foreach ($service as $value) 
	{
		$order = get('consumed_order', 'order_id', $value['order_id']);
		if ($order) 
		{
			if ($value['service_type'] == 1) //1 店内服务 2 酒水饮料
			{
				if ($type == 1)
				{
					if ($order[0]['end_time'] <= $day_end && $order[0]['end_time'] >= $day_begin) 
					{
						$todaysalary +=$value['ticheng']/100;//技师项目提成
					}
					if ($order[0]['end_time'] <= $month_end && $order[0]['end_time'] >= $month_begin) 
					{
					   $salary +=$value['ticheng']/100;
					}
				}
				else if ($type == 2)
				{
					if ($order[0]['end_time'] <= $day_end && $order[0]['end_time'] >= $day_begin) 
					{
						$todaysalary += $value['jd_ticheng']/100;//接待项目提成
					}
					if ($order[0]['end_time'] <= $month_end && $order[0]['end_time'] >= $month_begin) 
					{
					   $salary += $value['jd_ticheng']/100;
					}
				}
			}
		}
    }
	
	$today_recharge_bonus = get_recharge_bonus($id,$day_begin,$day_end,$type);
	$month_recharge_bonus = get_recharge_bonus($id,$month_begin,$month_end,$type);
	
    return ['status' => 1, 'todaysalary' => $todaysalary + $today_recharge_bonus, 'salary' => $salary + $month_recharge_bonus];//今日提成，本月提成，佣金不算在提成里面
}

?>
