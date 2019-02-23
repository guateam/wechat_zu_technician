<?php
require("database.php");
$dir = $_SERVER['DOCUMENT_ROOT']."/vcr/ori/";
$vcr_dir = $_SERVER['DOCUMENT_ROOT']."/vcr/";
$save_dir = "/vcr/";//保存到数据库的路径
$img_dir =$_SERVER['DOCUMENT_ROOT']."/photo/";

//判断操作系统
$osname = PHP_OS;
if(strpos($osname,"Linux")!==false){
    $osname = 'Linux';
}else if(strpos($osname,"WIN")!==false){
    $osname = 'Windows';
}
$job_number =$_POST['job_number'];
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);        // 获取文件后缀名

$dict=['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u',
        'v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P',
        'Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0'];
$rnd_str = "";
for($i = 0;$i<7;$i++)
{
    $idx = rand(0,count($dict)-1);
    $rnd_str.=$dict[$idx];    
}

if ($_FILES["file"]["type"] == "video/mp4" ||  $_FILES["file"]["type"] == "video/quicktime")
{
    if ($_FILES["file"]["error"] > 0)
    {
        echo json_encode(['state'=>$_FILES["file"]["error"]]);
        exit();
    }
    else
    {
        // 判断当期目录下的 upload 目录是否存在该文件
        // 如果没有 upload 目录，你需要创建它，upload 目录权限为 777
        if (file_exists( $dir.$_FILES["file"]["name"]))
        {
            echo json_encode(['state'=>"文件已经存在或路径不存在"]);
            exit();
        }
        else
        {
            $tmstmp = date("ymdhis",time());//时间戳
            //图片的名字,包含要保存到的路径，用于创建文件和命令行参数
            $img_name = $img_dir.$rnd_str.$tmstmp;//放到图片文件夹
            //保存在数据库中的路径格式
            $sv_img_name = '/photo/'.$rnd_str.$tmstmp.'.jpg';
            $sv = $save_dir.$rnd_str.$tmstmp.'.'.$extension;//保存到数据库的路径
            $tm = $dir.$rnd_str.$tmstmp.'.'.$extension;//$rnd_str 随机7位字符串
			$tm2 = $vcr_dir.$rnd_str.$tmstmp.'.'.$extension;//$rnd_str 随机7位字符串
			
			//保存文件到某文件夹	
            move_uploaded_file($_FILES["file"]["tmp_name"],$tm );//已经上传成功！
            //根据系统获取视频第一帧图片，作为视频封面
            $osname = PHP_OS;
            if(strpos($osname,"Linux")!==false)
			{
                $osname = 'Linux';
				
				//----------------------------------------------------------------------------------
				$cmd="/monchickey/ffmpeg/bin/ffmpeg -i ".$tm." -f mjpeg -y -ss 3 -t 1 ".$img_name.".jpg";	//视频封面截图 和服务器安装ffmpeg的路径关联
				$ffmpeg_output2 = shell_exec($cmd);
				
				//---------------------------------------------
				$myfile = fopen("sdr2.txt", "a+") or die("Unable to open file!");				
				fwrite($myfile, "video cmd = ".$cmd."   "."\r\n");
				fwrite($myfile, "ffmpeg_output2 = ".$ffmpeg_output2."   "."\r\n");
				fclose($myfile);
				//---------------------------------------------
				//----------------------------------------------------------------------------------
				
				//----------------------------------------------------------------------------------
                //获取视频时长
				$cmd2 = "/monchickey/ffmpeg/bin/ffmpeg -i \"$tm\" 2>&1";//和服务器安装ffmpeg的路径关联
                $ffmpeg_output = shell_exec($cmd2);				
				
				//---------------------------------------------
				$myfile = fopen("sdr2.txt", "a+") or die("Unable to open file!");	
				fwrite($myfile, "video cmd2 = ".$cmd2."   "."\r\n");
				fwrite($myfile, "ffmpeg_output = ".$ffmpeg_output."   "."\r\n");
				fclose($myfile);
				//---------------------------------------------
				
                if( preg_match('/.*Duration: ((\d+)(?:\:)(\d+)(?:\:)(\d+))*/i', $ffmpeg_output, $matches) ) 
				{
                    //超过10秒则停止上传，并提示时长超出
                    // if ($matches[1] > "00:00:10" )
					// {
                        // unlink($tm);//删除视频文件
                        // echo json_encode(['state'=>"视频时长不能超过10秒"]);
                        // exit();
                    // }
					
					 //超过10秒截取前10秒
					 if ($matches[1] > "00:00:10" )
					 {
						$cmd3 = "/monchickey/ffmpeg/bin/ffmpeg  -i $tm -vcodec copy -acodec copy -ss 00:00:00 -to 00:00:10 $tm2";
						$ffmpeg_output_2 = shell_exec($cmd3);	
						 
						//---------------------------------------------
						$myfile = fopen("sdr2.txt", "a+") or die("Unable to open file!");	
						fwrite($myfile, "cmd3 = ".$cmd3."   "."\r\n");
						fwrite($myfile, "ffmpeg_output_2 = ".$ffmpeg_output."   "."\r\n");
						fclose($myfile);
						//---------------------------------------------
					 }
                } 
				//----------------------------------------------------------------------------------
            }
			else if(strpos($osname,"WIN")!==false)
			{
                $osname = 'Windows';
                $cmd=$_SERVER['DOCUMENT_ROOT']."/ffmpeg/bin/ffmpeg.exe -i ".$tm." -f image2 -y ".$img_name.".jpg";
                //获取视频时长
                $ffmpeg_output = shell_exec($_SERVER['DOCUMENT_ROOT']."/ffmpeg/bin/ffmpeg.exe -i \"$tm\" 2>&1");
                if( preg_match('/.*Duration: ((\d+)(?:\:)(\d+)(?:\:)(\d+))*/i', $ffmpeg_output, $matches) ) {
                    //超过10秒则停止上传，并提示时长超出
                    if ($matches[1] > "00:00:10" ){
                        unlink($tm);
                        echo json_encode(['state'=>"视频时长不能超过10秒"]);
                        exit();
                    }
                } 
                exec($cmd,$result);
                
            }
			add("technician_video",[['job_number',$job_number],['dir',$sv],['time',time()],['poster',$sv_img_name]]);
            $tp_vdo = (get("technician_video",'dir',$sv))[0];
            echo json_encode(["state"=>1,'url'=>$sv,"ID"=>$tp_vdo['ID'],'poster'=>$tp_vdo['poster']]);
            exit();
        }
    }
}
else
{
    echo json_encode(['state'=>"不支持上传该类型的文件，仅支持Mp4和mov格式"]);
}

?>