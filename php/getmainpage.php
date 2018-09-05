<?php
require("database.php");
require("getsalary.php");
require("getsalarychart.php");
require("getclock.php");
require("gettechnician.php");
require "getcheckinstatus.php";
$id=$_POST['id'];

echo(json_encode(['status'=>1,'data'=>['app1'=>gettechnician($id),'app2'=>getsalary($id),'app3'=>getclock($id),'chart'=>getsalarychart($id),'checkin'=>getcheckinstatus($id)]]));