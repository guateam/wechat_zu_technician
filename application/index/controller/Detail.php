<?php
namespace app\index\controller;
use think\Controller;

class Detail extends Controller{
    public function index(){
        $id='AKB48';
        
        return $this->fetch('detail');
        return $this->error('404 未知技师,请注册','index/login/index');
    }
}