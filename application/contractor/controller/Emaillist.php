<?php
namespace app\contractor\controller;
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
    public function index()
    {
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $email=new \app\api\controller\Email();
            $data=$email->getemaillistforcontractor($userid);
            $this->assign('data',$data);
            return $this->fetch('email-list');
        }
        return $this->error('404 未知发包人员');
    }
}
