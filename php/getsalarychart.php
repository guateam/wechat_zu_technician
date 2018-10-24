<?php
function getsalarychart($id)
{
    $service = get('service_order', 'job_number', $id);
    if ($service) 
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
                    if ($order[0]['generated_time'] >= date('Y-m-d', (time() - ($i * 24 * 60 * 60))) . ' 00:00:00' && $order[0]['generated_time'] <= date('Y-m-d', (time() - ($i * 24 * 60 * 60))) . ' 23:59:59') 
					{
                        if ($order[0]['state'] == 4 || $order[0]['state'] == 5) 
						{
                            if ($value['service_type'] == 1) 
							{
                                $servicetype = get('service_type', 'ID', $value['item_id']);
                                if ($servicetype) 
								{
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