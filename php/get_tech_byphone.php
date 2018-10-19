<?php
require "database.php";
$phone = $_POST['phone'];
$tech = get("technician","phone_number",$phone);
if($tech)
{
    echo json_encode([
        'status'=>1,
        "job_number"=>$tech[0]['job_number']
    ]);
}
else
{
    echo json_encode([
        'status'=>0
    ]);
}

?>