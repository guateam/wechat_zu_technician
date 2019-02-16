<?php
require("database.php");
date_default_timezone_set('PRC');
$id = $_POST['id'];

del("friend_circle","ID",$id);
echo json_encode(['status'=>1]);
?>