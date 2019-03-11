<?php

	date_default_timezone_set('PRC');
	
	function getMonthNum( $date1, $date2, $tags='-' )
	{
		$date1 = explode($tags,$date1);
		$date2 = explode($tags,$date2);
		return abs($date1[0] - $date2[0]) * 12 - $date2[1] + abs($date1[1]);
	}

	function get_recharge_bonus($job_number,$begin,$end,$tech_type)
	{
		$monthCount = getMonthNum( date('Y-m-d',$end), date('Y-m-d',$begin)) + 1;

		$recharge = 0;
		$recharge_ticheng = 0;//充卡提成
		//$rcg = sql_str("select * from recharge_record where job_number='$job_number' and type = 1 and generated_time >= $begin and generated_time <= $end");			
		$rcg = sql_str("select * from chongka_record where job_number='$job_number' and type = 1 and generated_time >= $begin and generated_time <= $end");	
		
		if ($rcg)//这里查到的是 某个技师 在时间段内的充卡金额
		{
			$bonus = sql_str("select * from recharge_bonus order by recharge desc");
			if ($bonus)
			{
				foreach ($rcg as $eachrecharge)//对每一笔充值进行循环                    
				{
					$recharge = $recharge + $eachrecharge['charge'];
					
					for($i =0 ; $i < count($bonus); $i++)//对每一级别的充卡提成进行循环
					{
						if ($eachrecharge['charge'] >= $bonus[$i]['recharge'] * 100 * $monthCount)//倒排序
						{
							if ($tech_type == 1)
							{
								$recharge_ticheng += $bonus[$i]['tech_bonus'] * 100 * $monthCount;  
							}
							else if ($tech_type == 2)
							{
								$recharge_ticheng += $bonus[$i]['jiedai_bonus'] * 100 * $monthCount;   
							}
							else if ($tech_type == 3)
							{
								$recharge_ticheng = $bonus[$i]['cashier_bonus'] * 100 * $monthCount;
							}
							break; ////跳出 每一级别的充卡提成循环
						}
					}
				}
			}
		}
		return $recharge_ticheng / 100;
	}
?>