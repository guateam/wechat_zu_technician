<?php
require("database.php");

$job_number=$_POST['job_number'];

$photos = get("technician_photo","job_number",$job_number);
$result = [];
if($photos)
{
    foreach($photos as $photo)
	{
        array_push($result,['url'=>$photo['img']]);
    }
}
echo json_encode($result);

?>