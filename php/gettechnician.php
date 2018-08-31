<?php
require("database.php");

$id=$_POST['id'];

$technician=get('technician','job_number',$id);
$shop = get("shop");
if($technician){
    echo(json_encode(['status'=>1,'name'=>$technician[0]['name'],'shop'=>$shop[0]['name']]));
}else{
    echo(json_encode(['status'=>0]));
}
