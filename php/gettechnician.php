<?php
require("database.php");

$id=$_POST['id'];

$technician=get('technician','job_number',$id);
if($technician){
    echo(json_encode(['status'=>1,'name'=>$technician[0]['name'],'shop'=>'lalala']));
}else{
    echo(json_encode(['status'=>0]));
}
