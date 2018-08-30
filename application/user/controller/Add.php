<?php
namespace app\user\controller;
use think\Controller;
//use think\db;
/**
 * 以后会整合到设置里
 * 不建议
 */
class Add extends Controller
{
    public function index(){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $username=$user->getusername($userid);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            return $this->error('404');
        }
        return $this->error('404 未知接包人员');
    }
}