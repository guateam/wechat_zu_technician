<?php
require('database.php');
$newest = false;
if(isset($_POST['newest']))
{
    $newest = true;
}
$notice = [];
if($newest)
    $notice = sql_str("select * from tech_notice order by date desc limit 1");
else
    $notice = sql_str("select * from tech_notice order by date desc");

echo json_encode(['status'=>1,'data'=>$notice]);

?>
