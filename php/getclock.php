<?php

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
                if ($value['clock_type'] == 2) 
				{
                    if ($order[0]['generated_time'] <= strtotime(date('Y-m-d') . ' 23:59:59') && $order[0]['generated_time'] >= strtotime(date('Y-m-d') . ' 00:00:00')) 
					{
                        $todayclock++;
                    }
                    if ($order[0]['generated_time'] <= strtotime(date('Y-m') . '-31 23:59:59') && $order[0]['generated_time'] >= strtotime(date('Y-m') . '-01 00:00:00')) 
					{
                        $clock++;
                    }
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