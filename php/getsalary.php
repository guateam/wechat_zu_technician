<?php

function getsalary($id)
{
    $service = sql_str("select A.* from service_order A,consumed_order B where A.job_number = '$id'and A.order_id=B.order_id and (B.state=4 or B.state=5)");
    //获取技师类型
    // $type = sql_str("select type from technician where job_number='$id'");
    // if ($type) {
    //     $type = $type[0]['type'];
    // }
    $todaysalary = 0;
    $salary = 0;
    foreach ($service as $value) 
	{
            $order = get('consumed_order', 'order_id', $value['order_id']);
            if ($order) 
			{
                    if ($value['service_type'] == 1) 
					{
                            $day_end = strtotime(date('Y-m-d') . ' 23:59:59');
                            $day_begin = strtotime(date('Y-m-d') . ' 00:00:00');
                            $month_begin = strtotime(date('Y-m') . '-01 00:00:00');
                            $month_end = strtotime(date('Y-m-t') . ' 23:59:59');
                            if ($order[0]['end_time'] <= $day_end && $order[0]['end_time'] >= $day_begin) 
							{
                                $todaysalary +=$value['ticheng']/100;
                            }
                            if ($order[0]['end_time'] <= $month_end && $order[0]['end_time'] >= $month_begin) 
							{
                               $salary +=$value['ticheng']/100;
                            }
                    }
            }
    }
    $invite_bonus = get_invite_bonus($id);
    return ['status' => 1, 'todaysalary' => $todaysalary+$invite_bonus['invite_today'], 'salary' => $salary+$invite_bonus['invite_month']];
}


function get_invite_bonus($job_number)
{
    $begin_today = date("Y-m-d 00:00:00",time());
    $end_today = date("Y-m-d 23:59:59",time());
    $begin_month = date("Y-m-01 00:00:00",time());
    $end_month = date("Y-m-t 23:59:59",time());

    $begin_today = strtotime($begin_today);
    $end_today = strtotime($end_today);
    $begin_month = strtotime($begin_month);
    $end_month = strtotime($end_month);

    $invited = get("inviteship", 'inviter_job_number', $job_number);

    $total_earn_today = 0;
    $total_earn_month = 0;

    if ($invited) 
	{
        foreach ($invited as $inv) 
		{
            //获取下家信息
            $fresh_jbnb = $inv['freshman_job_number'];
            //获取下家支付给本家的钱
            $yeji_today = get_lost($inv['freshman_job_number'], $begin_today, $end_today);
            $yeji_month = get_lost($inv['freshman_job_number'], $begin_month, $end_month);

            $total_earn_today += $yeji_today['lost'];
            $total_earn_month += $yeji_month['lost'];
        }
    }
    return [ 'invite_today' => $total_earn_today, 'invite_month'=>$total_earn_month];
}

function get_lost($job_number, $begin, $end)
{
    //该技师自己做的所有订单
    $so = sql_str("select A.* from service_order A,consumed_order B where A.job_number='$job_number' and B.order_id = A.order_id and B.end_time >=$begin and B.end_time <= $end and (B.state=4 or B.state=5)");
    //邀请该技师的人
    $self_p = sql_str("select * from inviteship where `freshman_job_number`='$job_number'");
    //付给邀请自己的人的钱
    $lost = 0;

    if ($so) 
	{
        foreach ($so as $idx => $svod) 
		{
            $item_id = $svod['item_id'];
            $order_id = $svod['order_id'];
            $consumed = sql_str("select state,end_time from consumed_order where order_id = '$order_id'");
            $name = sql_str("select name from service_type where ID='$item_id'");
            //计算支付给邀请人的钱
            $lost += $svod['yongjin']/ 100;
            $so[$idx] = array_merge($so[$idx], ['lost' => $svod['yongjin'] / 100,'name'=>$item[0]['name']]);
        }
        $so[$idx]['end_time'] = $consumed[0]['end_time'];
    }

    return ['lost' => $lost, 'order' => $so];
}

?>
