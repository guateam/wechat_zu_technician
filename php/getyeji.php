<?php
require("database.php");
//获取工号，然后查出类型
global $type;
$type = $_POST['type'];
$type = sql_str("select type from technician where job_number = '$type'");
if($type && count($type)>0){
    $type = $type[0]['type'];
}

function get_all_yeji(){
    global $type;
    //获取指定类型的技师列表
    $jbnb = sql_str("select * from technician where type='$type'");
    $result = [];
    foreach($jbnb as $job){
        $yeji = get_yeji($job['job_number']);
        array_push($result,$yeji);
    }
    //按照金额排序
    for($i=0;$i<count($result)-1;$i++){
        $biggest = $i;
        for($j=$i+1;$j<count($result);$j++){
            if($result[$biggest]['earn'] < $result[$j]['earn'] ){
                $biggest = $j;
            }
        }
        if($biggest != $i){
            $temp = $result[$biggest];
            $result[$biggest] = $result[$i];
            $result[$i] = $temp;
        }
    }
    return $result;
}

function get_yeji($job_number)
{
    global $type;
    $count = 0;
    if(isset($_POST['begin']))$begin = $_POST['begin'];
    if(isset($_POST['end']))$end = $_POST['end'];
    $begin = strtotime($begin);
    $end = strtotime($end);
    //获取在时间范围内的正确状态的订单
    $co = sql_str("select B.* from service_order A,consumed_order B where A.`job_number`='$job_number'and A.order_id=B.order_id and B.`end_time`>='$begin' and B.`end_time` <= '$end' and (B.state = 4 or B.state = 5) group by B.order_id");
    $so = sql_str("select A.* from service_order A,consumed_order B where A.`job_number`='$job_number'and A.order_id=B.order_id and B.`end_time`>='$begin' and B.`end_time` <= '$end' and (B.state = 4 or B.state = 5)");
    $price = 0;
    foreach($co as $idx => $cod)
	{
        //记录自己的提成
        $price += $cod['pay_amount'];  
    }
    return [
        'job_number'=>$job_number,
        'clock_num'=>count($so),
        'status'=>1,
        'earn'=>$price/100,
        ];
}

//获取所有技师业绩
if(!isset($_POST['job_number']))
{
    echo  json_encode(['status'=>1,'data'=>get_all_yeji()]);
}
//获取单独一个技师的业绩
else
{
    $job_numbers = $_POST['job_number'];
    $result = [];
    foreach($job_numbers as $job_number)
	{
        array_push($result,get_yeji($job_number));
    }
    echo json_encode(['status'=>1,'data'=>$result]);
}

?>
