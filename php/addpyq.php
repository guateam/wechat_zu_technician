<?php
require("database.php");
date_default_timezone_set('PRC');
$urls = [];
if(isset($_POST['urls']))$urls = $_POST['urls'];
$job_number = $_POST['job_number'];
//生成文件时候的路径
$dir = $_SERVER['DOCUMENT_ROOT']."/photo/";
//数据库里存的路径
$save_dir = "/photo/";

$time= time();
foreach($urls as $url)
{
    if (strstr($url,","))
	{
        $image = explode(',',$url);
        $url = $image[1];
    }
    $img = base64_decode($url);
    //产生随机文件名
    $dict=['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u',
        'v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P',
        'Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0'];
    $rnd_str = "";
    for($i = 0;$i<7;$i++)
    {
        $idx = rand(0,count($dict)-1);
        $rnd_str.=$dict[$idx];    
    }
    $rnd_str.=date("is",time());
    $sv = $dir.$rnd_str.'.jpg';
    file_put_contents($sv, $img);//上传的图片名称随机生成 $sv
	
	//------------------------------------------------------------
	$osname = PHP_OS;	
	if(strpos($osname,"Linux")!==false)
	{
		$cmd="jpegoptim -m 20 ".$sv;
		
		//---------------------------------------------
		$myfile = fopen("sdr2.txt", "a+") or die("Unable to open file!");				
		fwrite($myfile, "cmd = ".$cmd."   "."\r\n");
		fclose($myfile);
		//---------------------------------------------
		exec($cmd,$result);
	}
	//------------------------------------------------------------
	
    add('technician_photo',[['img',$save_dir.$rnd_str.'.jpg'],['time',$time],['job_number',$job_number]]);//存入数据库
}

$ids = sql_str("select group_concat(ID) as id from technician_photo where `time` = '$time'");
//存进数据库的图片ID序列
$img = "";
if(!is_null($ids[0]['id']))$img = $ids[0]['id'];
//视频暂时先放一下
$video = $_POST['video'];
$content = $_POST['content'];
add("friend_circle",[['content',$content],['img',$img],['video',$video],['job_number',$job_number],['date',$time]]);
echo json_encode(['status'=>1]);

?>