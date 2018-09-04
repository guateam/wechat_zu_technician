<?php

function getsalarychartold($id)
{
    $service = get('service_order', 'job_number', $id);
    if ($service) {
        $time = [];
        $data = [];
        $i = 0;
        foreach ($service as $value) {
            $order = get('consumed_order', 'order_id', $value['order_id']);
            if ($order) {
                if ($value['service_type'] == 1) {
                    $servicetype = get('service_type', 'ID', $value['item_id']);
                    if ($servicetype) {
                        if (count($time) == 0) {
                            $date = substr($order[0]['generated_time'], 0, strpos($order[0]['generated_time'], ' '));
                            array_push($time, $date);
                            array_push($data, $servicetype[0]['commission'] / 100);
                        } else if ($time[$i] . ' 00:00:00' <= $order[0]['generated_time'] && $time[$i] . ' 23:59:59' >= $order[0]['generated_time']) {
                            $data[$i] += $servicetype[0]['commission'] / 100;
                        } else {
                            $date = substr($order[0]['generated_time'], 0, strpos($order[0]['generated_time'], ' '));
                            array_push($time, $date);
                            array_push($data, $servicetype[0]['commission'] / 100);
                            $i++;
                        }
                    }
                }
            }
        }
        $time1 = [];
        foreach ($time as $value) {
            array_push($time1, date('m-d', strtotime($value)));
        }
        $time = $time1;
        for ($i = 0; $i < count($time); $i++) {
            $k = $i;
            for ($j = $i + 1; $j < count($time); $j++) {
                if ($time[$i] > $time[$j]) {
                    $k = $j;
                }
            }
            if ($k != $i) {
                $temp1 = $time[$i];
                $time[$i] = $time[$k];
                $time[$k] = $temp1;
                $temp2 = $data[$i];
                $data[$i] = $data[$k];
                $data[$k] = $temp2;
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
    } else {
        return ['status' => 0];
    }
}

function getsalarychart($id)
{
    $service = get('service_order', 'job_number', $id);
    if ($service) {
        $time = [];
        $data = [];
        for ($i = 0; $i < 8; $i++) {
            array_unshift($time, date('m-d', (time() - ($i * 24 * 60 * 60))));
            array_unshift($data, 0);
            foreach ($service as $value) {
                $order = get('consumed_order', 'order_id', $value['order_id']);
                if ($order) {
                    if ($order[0]['generated_time'] >= date('Y-m-d', (time() - ($i * 24 * 60 * 60))) . ' 00:00:00' && $order[0]['generated_time'] <= date('Y-m-d', (time() - ($i * 24 * 60 * 60))) . ' 23:59:59') {
                        if ($order[0]['state'] == 4 || $order[0]['state'] == 5) {
                            if ($value['service_type'] == 1) {
                                $servicetype = get('service_type', 'ID', $value['item_id']);
                                if ($servicetype) {
                                    $data[0] += $servicetype[0]['commission'] / 100;
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
    }else{
        return ['status' => 1, 'data' => [
            'series' => [[
                'data' => [],
            ]],
            'xAxis' => [
                'data' => [],
            ],
        ]];
    }
}
