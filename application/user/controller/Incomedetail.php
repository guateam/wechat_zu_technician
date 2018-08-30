<?php
namespace app\user\controller;
use think\Controller;
//use think\db;
/**
 * 收入明细
 * 未实装
 * 界面未设计
 */
class Incomedetail extends Controller
{
    public function index(){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $username=$user->getusername($userid);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            return $this->fetch('incomedetail');
        }
        return $this->error('404 未知接包人员');
    }
}