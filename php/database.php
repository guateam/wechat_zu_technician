<?php


function get($table,$label="",$value=""){
    static $con = null;
    if(!$con)
        $con = mysqli_connect("localhost","root","smhhyyz508234","wechat_zu");
    if (!$con)
    {
        die('Could not connect: ' . mysql_error());
    }
    $statement="";

    if($label=="" && $value=="")$statement = "SELECT * FROM $table";
    else{
        $statement = "SELECT * FROM $table WHERE $label = '$value'";
    }
    $result = $con->query($statement);
    if($result->num_rows<=0)return [];

    $data=[];
    while($row = $result->fetch_assoc()){
        array_push($data,$row);
    }
    return $data;

}


function get_limit($table,$index=0,$label="",$value=""){
    static $con = null;
    if(!$con)
        $con = mysqli_connect("localhost","root","smhhyyz508234","wechat_zu");
    if (!$con)
    {
        die('Could not connect: ' . mysql_error());
    }
    $statement="";

    if($label=="" && $value=="")$statement = "SELECT * FROM $table LIMIT $index,20";
    else{
        $statement = "SELECT * FROM $table WHERE $label = '$value'";
    }
    $result = $con->query($statement);
    if($result->num_rows<=0)return [];

    $data=[];
    while($row = $result->fetch_assoc()){
        array_push($data,$row);
    }
    return $data;

}
?>