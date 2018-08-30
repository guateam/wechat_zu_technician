<?php
namespace app\user\controller;
use think\Controller;
//use think\db;
/**
 * 成果文件提交界面
 * 未实装
 * 好像还有bug
 */
class Send extends Controller
{
    public function index(){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $username=$user->getusername($userid);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            return $this->fetch('send');
        }
        return $this->error('404 未知接包人员');
    }
}