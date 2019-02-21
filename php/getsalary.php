<?php

function getsalary($id)
{
    $service = get('service_order', 'job_number', $id);
    //获取技师类型
    $type = sql_str("select type from technician where job_number='$id'");
    if ($type) {
        $type = $type[0]['type'];
    }

    
    $todaysalary = 0;
    $salary = 0;
    if ($service) {
        foreach ($service as $value) {
            $order = get('consumed_order', 'order_id', $value['order_id']);
            if ($order) {
                if ($order[0]['state'] == 4 || $order[0]['state'] == 5) {
                    if ($value['service_type'] == 1) {
                        $servicetype = get('service_type', 'ID', $value['item_id']);
                        if ($servicetype) {
                            $day_end = strtotime(date('Y-m-d') . ' 23:59:59');
                            $day_begin = strtotime(date('Y-m-d') . ' 00:00:00');
                            $month_begin = strtotime(date('Y-m') . '-01 00:00:00');
                            $month_end = strtotime(date('Y-m-t') . ' 23:59:59');

                            if ($order[0]['end_time'] <= $day_end && $order[0]['end_time'] >= $day_begin) {
                                //技师
                                if ($type == 1) {
                                    //排钟
                                    if($value['clock_type'] == 1)
                                        $todaysalary += ($servicetype[0]['pai_commission'] / 100);
                                    //点钟
                                    else
                                        $todaysalary += ($servicetype[0]['commission'] / 100);

                                //接待
                                } else if ($type == 2) {
                                    //排钟
                                    if($value['clock_type'] == 1)
                                        $todaysalary += ($servicetype[0]['pai_commission2'] / 100);
                                    //点钟
                                    else
                                        $todaysalary += ($servicetype[0]['commission2'] / 100);
                                }
                            }
                            if ($order[0]['end_time'] <= $month_end && $order[0]['end_time'] >= $month_begin) {
                                //技师
                                if ($type == 1) {
                                //排钟
                                if($value['clock_type'] == 1)
                                    $salary += ($servicetype[0]['pai_commission'] / 100);
                                //点钟
                                else
                                    $salary += ($servicetype[0]['commission'] / 100);

                                //接待
                                } else if ($type == 2) {
                                    //排钟
                                    if($value['clock_type'] == 1)
                                        $salary += ($servicetype[0]['pai_commission2'] / 100);
                                    //点钟
                                    else
                                        $salary += ($servicetype[0]['commission2'] / 100);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    $invite_bonus = get_invite_bonus($id);
    return ['status' => 1, 'todaysalary' => $todaysalary+$invite_bonus['invite_today'], 'salary' => $salary+$invite_bonus['invite_month']];

}


function get_invite_bonus($job_number){
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

    if ($invited) {
        foreach ($invited as $inv) {
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
    $so = sql_str("select A.* from service_order A,consumed_order B where A.job_number='$job_number' and B.order_id = A.order_id and B.end_time >=$begin and B.end_time <= $end ");
    //邀请该技师的人
    $self_p = sql_str("select * from inviteship where `freshman_job_number`='$job_number'");
    //付给邀请自己的人的钱
    $lost = 0;

    if ($so) {
        foreach ($so as $idx => $svod) {
            $item_id = $svod['item_id'];
            $order_id = $svod['order_id'];
            $consumed = sql_str("select state,end_time from consumed_order where order_id = '$order_id'");

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
        }
        $so[$idx]['end_time'] = $consumed[0]['end_time'];
    }

    return ['lost' => $lost, 'order' => $so];
}
