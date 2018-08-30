<?php
namespace k1;
function getmsg(){
    echo '123';
}
const NM='233';
namespace k2;
function getmsg(){
    echo '456';
    echo NM;
}
const NM='466';
\k2\getmsg();
echo \k1\NM;
?>