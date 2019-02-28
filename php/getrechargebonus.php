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
		$rcg = sql_str("select sum(charge) from recharge_record where job_number='$job_number' and generated_time >= $begin and generated_time <= $end");		
		if ($rcg)//这里查到的是 某个技师 在时间段内的充卡金额
		{
			if ($rcg[0]['sum(charge)'])
			{
				$recharge = $rcg[0]['sum(charge)'];
				//$tech_type  技师 接待 收银 technician 表type=1 技师 =2 接待 =3 收银
				$bonus = sql_str("select * from recharge_bonus order by recharge desc");
				if ($bonus)
				{
					for($i =0 ; $i < count($bonus); $i++)
					{
						if ($tech_type == 1)//0
						{
							if($recharge >= ( $bonus[$i]['tech_bonus'] * 100 * $monthCount) ) 
							{
								$recharge_ticheng = $bonus[$i]['tech_bonus'] * 100 * $monthCount;
								break;
							}
						}
						else if ($tech_type == 2)
						{
							if($recharge >= ( $bonus[$i]['jiedai_bonus'] * 100 * $monthCount) ) 
							{
								$recharge_ticheng = $bonus[$i]['jiedai_bonus'] * 100 * $monthCount;
								break;
							}
						}
						else if ($tech_type == 3)
						{
							if($recharge >= ( $bonus[$i]['cashier_bonus'] * 100 * $monthCount) ) 
							{
								$recharge_ticheng = $bonus[$i]['cashier_bonus'] * 100 * $monthCount;
								break;
							}
						}
					}
				}
			}
		}
		return $recharge_ticheng / 100;
	}
?>