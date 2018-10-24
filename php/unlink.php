<?php
$dirs = $_POST['dir'];
foreach($dirs as $dir){
    unlink($_SERVER['DOCUMENT_ROOT'].$dir['url']);
}