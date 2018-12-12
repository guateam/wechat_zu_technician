<?php
require("database.php");
function get_all_yeji(){
    //获取工号，然后查出类型
    $type = $_POST['type'];
    $type = sql_str("select type from technician where job_number = '$type'");
    if($type && count($type)>0){
        $type = $type[0]['type'];
    }

    //获取指定类型的技师列表
    $jbnb = sql_str("get * from technician where type='$type'");
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
    if(isset($_POST['begin']))$begin = $_POST['begin'];
    if(isset($_POST['end']))$end = $_POST['end'];
    $begin = strtotime($begin);
    $end = strtotime($end);
    //获取在时间范围内的订单
    $so = sql_str("select * from service_order where `job_number`='$job_number' and `appoint_time`>='$begin' and `appoint_time` <= '$end' ");
    $price = 0;
    if($so)
	{
        foreach($so as $idx => $svod)
		{
            $item_id = $svod['item_id'];
            $commission = sql_str("select commission from service_type where `ID`='$item_id'");
            //记录自己的营业金额
            $price+=intval($commission[0]['commission'])/100;       
        }
    }
    return [
        'job_number'=>$job_number,
        'clock_num'=>count($so),
        'status'=>1,
        'earn'=>$price,
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
