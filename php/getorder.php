<?php
	require "database.php";

	$job_number = $_POST["job_number"];

	//找到对应工号的技师的类别
	$type = sql_str("select type from technician where job_number = '$job_number'");

	if ($type && count($type) > 0) 
	{
		$type = (int)$type[0]['type'];
	}

	//----------------------------------------
	$date = $_POST["date"];
	if($date == "")
	{
		$date = date("Y-m-01 00:00:00",time());
	}
	else
	{
		$date.=" 00:00:00";
	}
	//$date = strtotime($date);
	$date = $date + 9 * 3600;

	$date2 = "";
	if (!isset($_POST["date2"])) 
	{
		$date2 = date("Y-m-t 23:59:59",time());
	} 
	else 
	{
		$date2 = $_POST["date2"]." 23:59:59";
	}
	$date2 = strtotime($date2);
	$date2 = $date2 + 9 * 3600;

	//----------------------------------------
	
	if ($type == 1)//技师
	{
		$service_order = sql_str("select * from service_order where job_number = '$job_number' order by appoint_time");
		
		//$service_order = get("service_order", "job_number", $job_number);
		if (!$service_order) 
		{
			$service_order = [];
		}
	}
	else if ($type == 2)//接待
	{
		$service_order = sql_str("select * from service_order where jd_number = '$job_number' order by appoint_time");
		
		//$service_order = get("service_order", "jd_number", $job_number);
		if (!$service_order) 
		{
			$service_order = [];
		}
	}
	
	//----------------------------------------

	$consumed_order = [];
	$service_type = get("service_type");
	if (!$service_type) 
	{
		$service_type = [];
	}

	$dian = 0;
	$pai = 0;
	$last_soid = "";
	$key_info = [];
	//提成
	$ticheng = 0;
	//业绩
	$yeji = 0;
	$all_time = false;
	if ($date == "") 
	{
		$all_time = true;
	}

	foreach ($service_order as $so) 
	{
		$one_consumed_order = get("consumed_order", "order_id", $so['order_id']);
		
		$time = $one_consumed_order[0]['end_time'];
		if ($all_time) 
		{
			$date = $time;
			$date2 = $time;
		}
		
		//----------------------------------------------------------------
		$room = get("private_room", "id", $so['private_room_number']);//得到房间号
		if ($room)
		{
			$room_number = $room[0]['name'];
		}
		else 
		{
			$room_number = "";
		}
		//----------------------------------------------------------------
		
		if ($so['service_type'] < 3 && ($time >= $date && $time <= $date2) && ($one_consumed_order[0]['state'] == 4 || $one_consumed_order[0]['state'] == 5)) 
		{        
			$tp = get("service_type","ID",$so['item_id']);

			if($so['clock_type'] == 1)
			{
				$pai++;
			}
			else if($so['clock_type'] == 2 || $so['clock_type'] == 3)
			{
				$dian++;
			}

			if ($type == 1)
			{
				//$yeji += $one_consumed_order[0]['pay_amount']/100;//不按照实际付款金额计算
				$yeji += $so['price'] / 100;//按照设定的项目活动价格来计算
			
				$ticheng += $so['ticheng'] / 100;

				array_push($key_info, [
						"service_name" => $tp[0]['name'],
						"room_number" => $room_number,
						"bonus" => (int) $so['ticheng']/100,
						"time" => $one_consumed_order[0]['end_time'],
						"order_id" => $so['order_id'],
						"income" => $so['price']/100,
						"clock_type" => $so['clock_type'],
				]);
			}
			else if ($type == 2)
			{
				$ticheng += $so['jd_ticheng'] / 100;

				array_push($key_info, [
						"service_name" => $tp[0]['name'],
						"room_number" => $room_number,
						"bonus" => (int) $so['jd_ticheng']/100,
						"time" => $one_consumed_order[0]['end_time'],
						"order_id" => $so['order_id'],
						"income" => $so['price']/100,
						"clock_type" => $so['clock_type'],
				]);
			}
		}
		if ($so['order_id'] != $last_soid) //这里是为了防止同一个订单的多个服务项目，而插入多次订单
		{
			array_push($consumed_order, $one_consumed_order[0]);
		}
		$last_soid = $so['order_id'];
	}

	echo json_encode((object) [
		'total_clock' => $pai + $dian,
		'dian'=>$dian,
		'pai'=>$pai,
		'bonus' => $ticheng,
		'total_income' => $yeji,
		'key_info' => $key_info,
		'type' => $type,
	]);

?>
