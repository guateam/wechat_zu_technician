<?php
namespace app\user\controller;
use think\Controller;
//use think\db;
/**
 * 历史记录页面
 * 已实装
 */
class History extends Controller
{
    public function index(){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $username=$user->getusername($userid);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            $project =new \app\api\controller\Project();
            $list=$project->gethistoryprojectbyuserid($userid);
            $this->assign('list',$list);
            return $this->fetch('history');
        }
        return $this->error('404 未知接包人员');
    }
}