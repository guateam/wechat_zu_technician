<?php
require("database.php");
$dir = "../photo/";
$job_number =implode("",$_POST);
$allowedExts = array("gif", "jpeg", "jpg", "png","PNG");
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);        // 获取文件后缀名

if ((
    ($_FILES["file"]["type"] == "image/jpeg") 
    ||  ($_FILES["file"]["type"] == "image/jpg")
    || ($_FILES["file"]["type"] == "image/x-png")
|| ($_FILES["file"]["type"] == "image/png"))   
){
    if ($_FILES["file"]["error"] > 0)
    {
        echo "错误：" . $_FILES["file"]["error"] . "<br>";
    }
    else
    {
        echo "上传文件名: " . $_FILES["file"]["name"] . "<br>";
        echo "文件类型: " . $_FILES["file"]["type"] . "<br>";
        echo "文件大小: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
        echo "文件临时存储的位置: " . $_FILES["file"]["tmp_name"];

        // 判断当期目录下的 upload 目录是否存在该文件
        // 如果没有 upload 目录，你需要创建它，upload 目录权限为 777
        if (file_exists( $dir . $_FILES["file"]["name"]))
        {
            echo $_FILES["file"]["name"] . " 文件已经存在。 ";
        }
        else
        {
            $tm = date("y-m-d-h-i-s",time());
            $tm=$dir.$tm.$_FILES["file"]["name"];
            // 如果 upload 目录不存在该文件则将文件上传到 upload 目录下
            if(count(get("technician_photo","job_number",$job_number))<5){
                move_uploaded_file($_FILES["file"]["tmp_name"],$tm );
                add("technician_photo",[['job_number',$job_number],['img',$tm]]);
                echo json_encode(["state"=>1,'url'=>$tm]);
            }
            else{
                echo json_encode(["state"=>0]);
            }

        }
    }

}

?>