<?php
namespace app\index\controller;
use think\Controller;

class Detail extends Controller{
    public function index(){
        $id='AKB48';
        if($id){
            $order=new \app\api\controller\Serviceorder();
            $data=$order->getdetaillistfortechnician($id);
            $sum=0;
            $salarysum=0;
            foreach($data as $value){
                $sum+=$value['price'];
                $salarysum+=$value['salary'];
            }
            $count=count($data);
            $this->assign('sum',$sum);
            $this->assign('salarysum',$salarysum);
            $this->assign('count',$count);
            $this->assign('data',$data);
            return $this->fetch('detail');
        }
        return $this->error('404 未知技师,请注册','index/login/index');
    }
}