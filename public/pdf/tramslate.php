<?php
require_once 'pdf.php';
$pdf = new office2pdf();
$input_url1 = 'C:\\AppServ\\www\\tp5\\public\\uploads\\resource\\'.$_GET['localaddress'];	//被转码文件的绝对地址
$output_url1 = 'C:\\AppServ\\www\\tp5\\public\\uploads\\resource\\'.$_GET['localaddress'].'.pdf';		//转码后文件的保存地址（绝对地址）
$pdf->run($input_url1,$output_url1);
return json_encode(['status'=>1]);
