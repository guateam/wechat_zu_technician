<?php
require "database.php";
$begin = $_POST['begin'];
$end = $_POST['end'];
$shopid = $_POST['shopid'];

$persent = 0;
/*
//获取技师能从充值当中获取的提成比例
$persent = sql_str("select recharge_income from shop where ID='$shopid'");
if ($persent) 
{
    $persent = (float) $persent[0]['recharge_income'];
}
*/

//是否在时间范围内搜索
$time_limit = true;

if ($begin == $end) 
{
    //如果两个时间是一样的，则表示一天内的时间段，添加更加细的时间表示
    if ($begin != "" && $end != "") 
	{
        $begin .= " 00:00:00";
        $end .= " 23:59:59";
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

$job_number = $_POST['job_number'];

$str = "";
if ($time_limit) 
{
    $str = "select A.charge/100 as charge ,A.generated_time as time,C.name,C.phone_number from recharge_record A,technician B,customer C where B.job_number='$job_number' and A.job_number=B.job_number and C.openid = A.user_id and A.type=1 and A.generated_time >= '$begin' && A.generated_time <= '$end' order by A.generated_time";
} 
else 
{
    $str = "select A.charge/100 as charge ,A.generated_time as time,C.name,C.phone_number from recharge_record A,technician B,customer C where B.job_number='$job_number' and A.job_number=B.job_number and C.openid = A.user_id and A.type=1 order by A.generated_time";
}

$records = sql_str($str);
//总充值额
$total_income = 0;
foreach ($records as $record) 
{
    $total_income += $record['charge'];
}

if ($records) 
{
    echo json_encode(['status' => 1, 'data' => $records, 'total_income' => (int) $total_income, 'total_bonus' => (int) $total_income * $persent]);
} 
else 
{
    echo json_encode(['status' => 0]);
}
?>