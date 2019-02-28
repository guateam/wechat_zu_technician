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
$end = strtotime($end);


$str = "";
if ($time_limit) 
{
	//连接3张表，recharge_record technician customer，条件是充值的钱
    $str = "select A.charge/100 as charge ,A.generated_time as time,C.name,C.phone_number from recharge_record A,technician B,customer C where B.job_number='$job_number' and A.job_number=B.job_number and C.openid = A.user_id and A.type=1 and A.generated_time >= '$begin' && A.generated_time <= '$end' order by A.generated_time";
} 
else 
{
    $str = "select A.charge/100 as charge ,A.generated_time as time,C.name,C.phone_number from recharge_record A,technician B,customer C where B.job_number='$job_number' and A.job_number=B.job_number and C.openid = A.user_id and A.type=1 order by A.generated_time";
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

/*
$records = sql_str($str);
//总充值额
$total_income = 0;
foreach ($records as $record) 
{
    $total_income += $record['charge'];
}
*/

$records = sql_str($str);

if ($records) 
{
    //echo json_encode(['status' => 1, 'data' => $records, 'total_income' => (int) $total_income, 'total_bonus' => (int) $total_income * $persent]);
	echo json_encode(['status' => 1, 'data' => $records, 'total_income' => (int) $recharge / 100, 'total_bonus' => (int) $recharge_ticheng / 100]);
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