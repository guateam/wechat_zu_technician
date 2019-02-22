<?php
require('database.php');
$job_number = $_POST['job_number'];
$tips = get("tip",'technician_id',$job_number);
$result = [];
$begin = null;
$end = null;
if(isset($_POST['begin']))$begin = $_POST['begin'];
if(isset($_POST['end']))$end = $_POST['end'];

if(!is_null($begin))
{
	$begin = strtotime($begin);
}

if(!is_null($end))
{
	$end = strtotime($end);
}

//---------------------------------------------
// $myfile = fopen("sdr.txt", "a+") or die("Unable to open file!");				
// fwrite($myfile, "begin = ".$begin."   "."\r\n");
// fwrite($myfile, "end = ".$end."   "."\r\n");
// fclose($myfile);
//---------------------------------------------

$t = time();

if(!is_null($begin) && !is_null($end))
{
   
	$str = "select ID,date,technician_id as job_number,user_id,salary from tip where `technician_id` = '$job_number' and date < '$end' and date > '$begin'";	
	$arrlist = sql_str($str);
	
	$num = count($arrlist);        
	for($i=0;$i<$num;$i++)
	{
		$openid = $arrlist[$i]['user_id'];
		$name = sql_str("select name from customer where openid='$openid'");
		if($name)$name = $name[0]['name'];
		else $name = '未知用户';
		$arrlist[$i] = array_merge($arrlist[$i],['username'=>$name]);
		$arrlist[$i]['date'] = date("Y-m-d H:i:s", $arrlist[$i]['date']);
		array_push($result,$arrlist[$i]);
	}
	
	
	echo json_encode(['status'=>1,'data'=>$result]);
}
else if (is_null($begin) && !is_null($end))
{
	$str = "select * from tip where `technician_id` = '$job_number' and date < '$end'";	
	$result = sql_str($str);
	
	echo json_encode(['status'=>1,'data'=>$result]);
}
else if (!is_null($begin) && is_null($end))
{
	$str = "select * from tip where `technician_id` = '$job_number' and date > '$begin'";	
	$result = sql_str($str);

	$num = count($result);        
	for($i=0;$i<$num;$i++)
	{
		$openid = $result[$i]['user_id'];
		$name = sql_str("select name from customer where openid='$openid'");
		if($name)$name = $name[0]['name'];
		else $name = '未知用户';
		$result[$i] = array_merge($result[$i],['username'=>$name]);
		$result[$i]['date'] = date("Y-m-d H:i:s", $result[$i]['date']);
	}
	echo json_encode(['status'=>1,'data'=>$result]);
}
else if (is_null($begin) && is_null($end))
{
	$result = sql_str("select * from tip where `technician_id` = '$job_number'");
	$result = sql_str($str);
	$num = count($result);        
	for($i=0;$i<$num;$i++)
	{
		$openid = $result[$i]['user_id'];
		$name = sql_str("select name from customer where openid='$openid'");
		if($name)$name = $name[0]['name'];
		else $name = '未知用户';
		$result[$i] = array_merge($result[$i],['username'=>$name]);
		$result[$i]['date'] = date("Y-m-d H:i:s", $result[$i]['date']);
	}
	echo json_encode(['status'=>1,'data'=>$result]);
}else
{
	echo json_encode(['status'=>0,'data'=>[]]);
}

?>
