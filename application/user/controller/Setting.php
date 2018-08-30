<?php
namespace app\user\controller;
use think\Controller;
//use think\db;
/**
 * 设置页面
 * 未实装
 * 界面未设计
 */
class Setting extends Controller
{
    public function index(){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $username=$user->getusername($userid);
            $data=$user->getuserdetail($userid);
            $this->assign('data',$data);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            return $this->fetch();
        }
        return $this->error('404 未知接包人员');
    }

    public function change($id,$name,$gender,$birthday,$idcard,$phonenumber,$company,$advantage,$emergencyname,$emergencyphone,$password){
        $user=new \app\api\controller\Users();
        $user->change($id,$name,$gender,$birthday,$idcard,$phonenumber,$company,$advantage,$emergencyname,$emergencyphone,$password);
        return json(['status'=>1]);
    }
}