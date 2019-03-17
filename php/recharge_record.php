<?php
require "database.php";
$begin = $_POST['begin'];
$end = $_POST['end'];
$shopid = $_POST['shopid'];
$job_number = $_POST['job_number'];

//是否在时间范围内搜索
$time_limit = true;

$begin .= " 00:00:00";
$end .= " 23:59:59";

if ($begin == $end) 
{
    //如果两个时间是一样的，则表示一天内的时间段，添加更加细的时间表示
    if ($begin != "" && $end != "") 
	{
        //$begin .= " 00:00:00";
        //$end .= " 23:59:59";
    }
    //如果时间为空，表示不在时间范围内搜索
    else 
	{
        $time_limit = false;
    }
}

//转换为时间戳
$begin = strtotime($begin);
$begin = $begin + 9 * 3600;

$end = strtotime($end);
$end = $end + 9 * 3600;


$str = "";
if ($time_limit) 
{
	 $str = "select A.charge,A.generated_time,B.mobile as phone_number,B.name  from chongka_record A, vipcard B where A.cardNo = B.cardNo and  generated_time >= '$begin' and generated_time <= '$end' and job_number = '$job_number' order by generated_time";
} 
else 
{
	 //$str = "select charge/100 as charge,generated_time,phone_number,user_name as name  from recharge_record where type = 1 and job_number = '$job_number' order by generated_time";
	 
	 $str = "select A.charge,A.generated_time,B.mobile as phone_number,B.name  from chongka_record A, vipcard B where A.cardNo = B.cardNo and type = 1 and job_number = '$job_number' order by generated_time";
}

/*
$persent = 0;

//获取技师能从充值当中获取的提成比例
$persent = sql_str("select recharge_income from shop where ID='$shopid'");
if ($persent) 
{
    $persent = (float) $persent[0]['recharge_income'];
}
*/

//找到对应工号的技师的类别
$type = sql_str("select type from technician where job_number = '$job_number'");

if ($type && count($type) > 0) 
{
    $tech_type = $type[0]['type'];//1为技师，2为接待
}

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
				/*
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
				*/
				
				if ($eachrecharge['charge'] >= $bonus[$i]['recharge'] * 100)//倒排序
				{
					if ($tech_type == 1)
					{
						$recharge_ticheng += $bonus[$i]['tech_bonus'] * 100;  
					}
					else if ($tech_type == 2)
					{
						$recharge_ticheng += $bonus[$i]['jiedai_bonus'] * 100;   
					}
					else if ($tech_type == 3)
					{
						$recharge_ticheng = $bonus[$i]['cashier_bonus'] * 100;
					}
					break; ////跳出 每一级别的充卡提成循环
				}
			}
		}
	}
}


$records = sql_str($str);

if ($records) 
{
	echo json_encode(['status' => 1, 'data' => $records, 'total_income' => (int) $recharge, 'total_bonus' => (int) $recharge_ticheng]);
} 
else 
{
    echo json_encode(['status' => 0]);
}

function getMonthNum( $date1, $date2, $tags='-' )
{
	$date1 = explode($tags,$date1);
	$date2 = explode($tags,$date2);
	return abs($date1[0] - $date2[0]) * 12 - $date2[1] + abs($date1[1]);
}
?>