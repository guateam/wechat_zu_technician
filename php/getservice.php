<?php

global $con ;
$con = mysqli_connect("localhost","root","smhhyyz508234","wechat_zu");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
$service_type = get("service_type");
$vip = get("vip_information");
$max_discount = 100;
foreach($vip as $lv){
    if($lv['discount_ratio'] < $max_discount) 
        $max_discount = $lv['discount_ratio'];
}
$type = [];
if(!$service_type){
    echo json_encode((object)['status'=>0,'data'=>[]]);
}else{
    foreach($service_type as $tp){
        array_push($type,[
            "name"=>$tp['name'],
            "info"=>$tp['info'],
            "duration"=>$tp['duration'],
            "price"=>$tp['price']/100,
            "discount"=>$tp['discount']/100
        ]);
    }
    echo json_encode((object)['status'=>1,'data'=>$type,'max_discount'=>$max_discount/100]);
}



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