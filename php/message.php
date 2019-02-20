<?php
    $host = "http://dingxin.market.alicloudapi.com";
    $phone = $_POST['phone'];
    $path = "/dx/sendSms";
    $code = "";
    
    for($i=0;$i<6;$i++){
        $code.=rand(0,9);
    }

    $method = "POST";
    $appcode = "dae44277a4164e25b238910f87614ca7";
    $headers = array();
    array_push($headers, "Authorization:APPCODE " . $appcode);
    $querys = "mobile=".$phone."&param=code%3A".$code."&tpl_id=TP1711063";
    $bodys = "";
    $url = $host . $path . "?" . $querys;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_FAILONERROR, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    if (1 == strpos("$".$host, "https://"))
    {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }
    $res = curl_exec($curl);
    echo json_encode(['status'=>1,'msg'=>$res,'code'=>$code]);
?>