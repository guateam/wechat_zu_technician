<?php
function getsalarychart($id)
{
    $service = get('service_order', 'job_number', $id);
    $tech = get('technician','job_number',$id);
    $today_begin = strtotime(date("Y-m-d 00:00:00",time()));
    $today_end = strtotime(date("Y-m-d 23:59:59",time()));

    if ($service && $tech) 
	{
        $time = [];
        $data = [];
        for ($i = 0; $i < 8; $i++) 
		{
            array_unshift($time, date('m-d', (time() - ($i * 24 * 60 * 60))));
            array_unshift($data, 0);
            foreach ($service as $value) 
			{
                $order = get('consumed_order', 'order_id', $value['order_id']);
                if ($order) 
				{
                    $begin = $today_begin - ($i * 24 * 60 * 60);
                    $end = $today_end - ($i * 24 * 60 * 60);

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
                                        if($tech[0]['type'] == 2)
                                            $data[0] += $servicetype[0]['commission'] / 100;
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
                'data' => [],
            ]],
            'xAxis' => [
                'data' => [],
            ],
        ]];
    }
}
?>