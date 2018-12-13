<?php
require("database.php");

$job_number =$_POST['job_number'];
$begin = $_POST['begin'];
$end = $_POST['end'];
$shopid = "";
if(isset($_POST['shopid'])){
    $shopid = $_POST['shopid'];
}
//获取技师能从充值当中获取的提成比例
$persent = sql_str("select recharge_income from shop where ID='$shopid'");
if ($persent) {
    $persent = (float) $persent[0]['recharge_income'];
}
$money = 0;
$count = 0;

$charge = get("recharge_record","job_number",$job_number);
if($charge)
{
    foreach($charge as $ch)
	{
        $time = $ch['generated_time'];
        $time = substr($time,0,10);
        if($time>=$begin && $time<=$end )
		{
            $money+=$ch['charge']/100;
            $count++;
        }
    }
    echo json_encode([
        'status'=>1,
        'count'=>$count,
        'charge'=>$money,
        'bonus'=>(int)$money*$persent,
    ]);
}
else
{
    echo json_encode([
        'status'=>0,
        'count'=>0,
        'charge'=>0,
        'bonus'=>0,
    ]);
}


?>