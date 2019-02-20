<?php

function getsalary($id)
{
    $service = get('service_order', 'job_number', $id);
    //获取技师类型
    $type = sql_str("select type from technician where job_number='$id'");
    if ($type) {
        $type = $type[0]['type'];
    }

    if ($service) {
        $todaysalary = 0;
        $salary = 0;
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

                            if ($order[0]['generated_time'] <= $day_end && $order[0]['generated_time'] >= $day_begin) {
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
                            if ($order[0]['generated_time'] <= $month_end && $order[0]['generated_time'] >= $month_begin) {
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
        return ['status' => 1, 'todaysalary' => $todaysalary, 'salary' => $salary];
    } else {
        return ['status' => 0];
    }
}
