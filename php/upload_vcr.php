<?php
require("database.php");
$dir = $_SERVER['DOCUMENT_ROOT']."/vcr/";
$save_dir = "/vcr/";
$img_dir =$_SERVER['DOCUMENT_ROOT']."/photo/";

//判断操作系统
$osname = PHP_OS;
if(strpos($osname,"Linux")!==false)
{
    $osname = 'Linux';
}
else if(strpos($osname,"WIN")!==false)
{
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
            $tm = date("ymdhis",time());
            //图片的名字,包含要保存到的路径，用于创建文件和命令行参数
            $img_name = $img_dir.$rnd_str.$tm;
            //保存在数据库中的路径格式
            $sv_img_name = '/photo/'.$rnd_str.$tm.'.jpg';
            $sv = $save_dir.$rnd_str.$tm.'.'.$extension;
            $tm=$dir.$rnd_str.$tm.'.'.$extension;
			//保存文件到某文件夹	
            move_uploaded_file($_FILES["file"]["tmp_name"],$tm );
            //根据系统获取视频第一帧图片，作为视频封面
            $osname = PHP_OS;
            if(strpos($osname,"Linux")!==false)
			{
                $osname = 'Linux';
                $cmd="ffmpeg -i ".$tm." -f image2 -y ".$img_name.".jpg";
                //获取视频时长
                $ffmpeg_output = shell_exec("ffmpeg -i \"$tm\" 2>&1");
                if( preg_match('/.*Duration: ((\d+)(?:\:)(\d+)(?:\:)(\d+))*/i', $ffmpeg_output, $matches) ) {
                    //超过10秒则停止上传，并提示时长超出
                    if ($matches[1] > "00:00:10" )
					{
                        unlink($tm);
                        echo json_encode(['state'=>"视频时长不能超过10秒"]);
                        exit();
                    }
                } 
                exec($cmd,$result);
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