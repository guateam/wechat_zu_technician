<?php
$job_number = $_POST["job_number"];
$date = $_POST["date"];
$date2="";
if(!isset($_POST["date2"]))$date2=$date;
else{
    $date2 = $_POST["date2"];
}
global $con ;
$con = mysqli_connect("localhost","root","smhhyyz508234","wechat_zu");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
$service_order = get("service_order","job_number",$job_number);
if(!$service_order)$service_order=[];
$consumed_order = [];
$service_type = get("service_type");
if(!$service_type)$service_type = [];
$last_soid = "";
$key_info = [];
//提成
$ticheng = 0;
//业绩
$yeji = 0;
$all_time = false;
if($date=="")$all_time=true;

foreach($service_order as $so){
    $one_consumed_order = get("consumed_order","order_id",$so['order_id']);
    $time = $one_consumed_order['generated_time'];
    $time = substr($time,0,10);
    if($all_time){
        $date = $time;
        $date2 = $time;
    }
    if($so['service_type']==1 && ($time >= $date && $time <= $date2) ){
        foreach($service_type as $tp){
            if($tp['ID']== $so['item_id']){
                $so['price']=$tp['price']*$tp['discount']/100.0;
                $so['price']/=100;
                $ticheng+=$tp['commission']/100;
                $yeji+=$so['price'];
                array_push($key_info,["service_name"=>$tp['name'],"room_number"=>$so['private_room_number'],"bonus"=>(int)$tp['commission']/100,
                "time"=>$one_consumed_order['generated_time'],"order_id"=>$so['order_id'],"income"=>$so['price'],"clock_type"=>$so['clock_type']]);
            }
        }
    }
    if($so['order_id']!=$last_soid){
        array_push($consumed_order,$one_consumed_order);
    };
    $last_soid = $so['order_id'];
}


echo json_encode((object)[
    'total_clock'=>count($key_info),
    'bonus'=>$ticheng,
    'total_income'=>$ticheng+$yeji,
    'key_info'=>$key_info
]);

// some code
mysqli_close($con);

function get($table,$label="",$value=""){
    global $con;
    $statement="";

    if($label=="" && $value=="")$statement = "SELECT * FROM $table";
    else{
        $statement = "SELECT * FROM $table WHERE $label = '$value'";
    }
    $result = $con->query($statement);
    if($result->num_rows<=0)return [];

    $data=[];
    while($row = $result->fetch_assoc()){
        array_push($data,$row);
    }
    if(count($data)==1)return $data[0];
    return $data;

}

?>