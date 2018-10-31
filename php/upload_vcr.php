<?php
require("database.php");
$dir = $_SERVER['DOCUMENT_ROOT']."/vcr/";
$save_dir = "/vcr/";
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
            $sv = $save_dir.$rnd_str.$tm.$_FILES["file"]["name"];
            $tm=$dir.$rnd_str.$tm.$_FILES["file"]["name"];
				
			move_uploaded_file($_FILES["file"]["tmp_name"],$tm );
			add("technician_video",[['job_number',$job_number],['dir',$sv],['time',time()]]);
            $tp_vdo_id = (get("technician_video",'dir',$sv))[0];
            $tp_vdo_id = $tp_vdo_id['ID'];
            echo json_encode(["state"=>1,'url'=>$sv,"ID"=>$tp_vdo_id]);
            exit();
        }
    }
}
else
{
    echo json_encode(['state'=>"不支持上传该类型的文件，仅支持Mp4格式"]);
}

?>