<?php
namespace app\user\controller;
use think\Controller;
//use think\db;
/**
 * 获取email列表
 * 有部分bug
 * 1不能按时间顺序排列
 * 2不能使用右上角x来实现已读
 * 2018-3-4 张煜
 */
class Emaillist extends Controller
{
    public function index(){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $username=$user->getusername($userid);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            $email=new \app\api\controller\Email();
            $data=$email->getemaillistforuser($userid);
            $this->assign('data',$data);
            return $this->fetch('email-list');
        }
        return $this->error('404 未知接包人员');
    }
}