<?php
require("database.php");
$shop = get("shop");
if($shop){
    echo json_encode([
        'name'=>$shop[0]['name'],
        'position'=>$shop[0]['position']
    ]);
}else{
    echo json_encode([
        'name'=>"",
        'position'=>""
    ]);
}

?>