<?php
function gettechnician($id)
{
    $technician=get('technician','job_number',$id);
    $shop = get("shop");
    if($technician)
	{
        return ['status'=>1,'name'=>$technician[0]['name'],'shop'=>$shop[0]['name']];
    }
	else
	{
        return ['status'=>0];
    }
    
}
?>