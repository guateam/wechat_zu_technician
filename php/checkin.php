<?php
require "database.php";

$id=$_POST['id'];
$ip=$_POST['ip'];
//是否为计时器检测
$interval = false;

// echo json_encode(['status'=>1]);
if(isset($_POST['interval']))$interval = $_POST['interval'];

$technician=get('technician','job_number',$id);
if($technician)
{
	// if(!$interval){
	//     add('attendance',[['job_number',$id],['sign_type',0]]);
	// }
	// echo(json_encode(['status'=>1]));
	
    $technician=$technician[0];
    $shop=get('shop');
    if($shop)
	{
        if($shop[0]['ip_address']==$ip)
		{
            $attendance=get('attendance','job_number',$id);
            if($attendance)
			{
                if(time()-strtotime($attendance[count($attendance)-1]['check_time'])<24*60*60&&$attendance[count($attendance)-1]['sign_type']==0)
				{
                    echo(json_encode(['status'=>-1]));
                }
				else
				{
                    // 手动签到进行记录，计时器检测通过不记录
                    if(!$interval)
					{
                        add('attendance',[['job_number',$id],['sign_type',0]]);
                    }
                    echo(json_encode(['status'=>1]));
                }
            }
			else
			{
                add('attendance',[['job_number',$id],['sign_type',0]]);
                echo(json_encode(['status'=>1]));
            }
        }
		else
		{
            echo(json_encode(['status'=>0]));
            //计时器检测到不在本店内，则为异常，记录下来
            if($interval)
                add('attendance',[["sign_type",2],['job_number',$id]]);
        }
    }
	else
	{
        echo(json_encode(['status'=>0]));
    }
}
else
{
    echo(json_encode(['status'=>0]));
}
