<?php
require("database.php");

$id=$_POST['id'];

$service=get('service_order','job_number',$id);
if($service){
    $todaysalary=0;
    $salary=0;
    foreach($service as $value){
        $order=get('consumed_order','order_id',$value['order_id']);
        if($order){
            if($order[0]['state']==4 || $order[0]['state']==5){
                if($value['service_type']==1){
                    $servicetype=get('service_type','ID',$value['item_id']);
                    if($servicetype){
                        if($order[0]['generated_time']<=date('Y-m-d').' 23:59:59' && $order[0]['generated_time']>=date('Y-m-d').' 00:00:00'){
                            $todaysalary+=($servicetype[0]['commission']/100);
                        }
                        if($order[0]['generated_time']<=date('Y-m').'-31 23:59:59' && $order[0]['generated_time']>=date('Y-m').'-01 00:00:00'){
                            $salary+=($servicetype[0]['commission']/100);
                        }
                    }
                }
            }
        }
    }
    echo(json_encode(['status'=>1,'todaysalary'=>$todaysalary,'salary'=>$salary]));
}else{
    echo(json_encode(['status'=>0]));
}
