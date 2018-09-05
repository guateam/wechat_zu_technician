<?php
require("database.php");
$dir = $_SERVER['DOCUMENT_ROOT']."/vcr/";
$save_dir = "/vcr/";
$job_number =implode("",$_POST);
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);        // 获取文件后缀名

$dict=['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u',
        'v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P',
        'Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0'];
$rnd_str = "";
for($i = 0;$i<7;$i++){
    $idx = rand(0,count($dict)-1);
    $rnd_str.=$dict[$idx];    
}

if ($_FILES["file"]["type"] == "video/mp4" ){
    if ($_FILES["file"]["error"] > 0)
    {
                    echo json_encode(['state'=>0]);
    }
    else
    {
        // 判断当期目录下的 upload 目录是否存在该文件
        // 如果没有 upload 目录，你需要创建它，upload 目录权限为 777
        if (file_exists( $dir . $_FILES["file"]["name"]))
        {
            echo json_encode(['state'=>0]);
        }
        else
        {
            $tm = date("ymdhis",time());
            $sv = $save_dir.$rnd_str.$tm.$_FILES["file"]["name"];
            $tm=$dir.$rnd_str.$tm.$_FILES["file"]["name"];
            // 如果 upload 目录不存在该文件则将文件上传到 upload 目录下
                $tech = get("technician",'job_number',$job_number);
                if($tech[0]['vcr']!='' || isset($tech[0]['vcr']) || !is_null($tech[0]['vcr']))
                        unlink($_SERVER['DOCUMENT_ROOT'].$tech[0]['vcr']);
                move_uploaded_file($_FILES["file"]["tmp_name"],$tm );
                set("technician",'job_number',$job_number,[['vcr',$sv]]);

                echo json_encode(["state"=>1,'url'=>$sv]);

        }
    }

}

?>