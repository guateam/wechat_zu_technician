<?php
namespace app\index\controller;
use think\Controller;

class Servicelist extends Controller{
    public function index(){
        return $this->fetch('service_list');
    }
}
