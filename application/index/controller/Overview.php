<?php
namespace app\index\controller;
use think\Controller;

class Overview extends Controller{
    public function index(){
        return $this->fetch('overview');
    }
}
