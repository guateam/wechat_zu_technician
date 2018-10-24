<?php
require("database.php");
$job_number = $_POST['job_number'];
$datas = get("friend_circle",'job_number',$job_number);
$i = 0;
foreach($datas as $data)
{
    $datas[$i]['img'] = explode(",", $data['img']);
    $j = 0;
    foreach($datas[$i]['img'] as $imgid)
	{
        $result = get("technician_photo",'ID',$imgid);
        if($result)
		{
            $datas[$i]['img'][$j] = $result[0]['img'];
        }
        $j++;
    }
    $tm1 = strtotime($datas[$i]['date']);
    $tm2 = time();
    $gap = $tm2-$tm1;
    // if($gap<60)$gap=$gap."秒前";
    // else{
    //     $gap/=60;
    //     if($gap<60)$gap=round($gap,0)."分钟前";
    //     else{
    //         $gap/=60;
    //         if($gap<24)$gap=round($gap,0)."小时前";
    //         else{
    //             $gap/=24;
    //             if($gap<=30)$gap=round($gap,0)."天前";
    //             else{
    //                 $gap/=30;
    //                 if($gap<=12)$gap=round($gap,0)."月前";
    //                 else{
    //                     $gap/=12;
    //                     if($gap>=1)$gap=round($gap,0)."年前";
    //                 }
    //             }
    //         }
    //     }
    // }
    $datas[$i]['date']=$gap;
    $i++;

}
$jb = get("technician",'job_number',$job_number);
$head = "";
$background = "";
if(!$jb)
{
    $jb = "";
}
else
{
    $jb = $jb[0];
    if(!is_null($jb['friend_circle_background']))$background = $jb['friend_circle_background'];
    if(!is_null($jb['photo']))$head = $jb['photo'];
}
echo json_encode(['status'=>1,'data'=>$datas,'head'=>$head,'background'=>$background]);

?>