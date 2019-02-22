<?php
require("database.php");
$index = $_POST['index'];
$service_type = get_limit("service_type",$index);
$vip = get("vip_information");
$max_discount = 100;
foreach($vip as $lv)
{
    if($lv['discount_ratio'] < $max_discount) 
        $max_discount = $lv['discount_ratio'];
}
$type = [];

if(!$service_type)
{
    echo json_encode((object)['status'=>0,'data'=>[]]);
}
else
{
    foreach($service_type as $tp)
	{
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
?>