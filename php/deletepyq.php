<?php
require("database.php");
date_default_timezone_set('PRC');
$id = $_POST['id'];
$result = sql_str("delete from friend_circle where ID='$id'");
if($result){
    echo json_encode(['status'=>1]);
    exit();
}
echo json_encode(['status'=>0]);
