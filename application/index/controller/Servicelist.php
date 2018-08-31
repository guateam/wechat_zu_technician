<?php
namespace app\index\controller;
use think\Controller;

class Servicelist extends Controller{
    public function index(){
        $ctrl =new \app\api\controller\Servicetype();
        $data = $ctrl->get_type();
        $this->assign('list',$data);
        return $this->fetch('service_list');
    }
}
