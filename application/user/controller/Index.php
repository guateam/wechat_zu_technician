<?php
namespace app\user\controller;
use think\Controller;
//use think\db;
/**
 * 当前进行项目列表界面
 * 已实装
 */
class Index extends Controller
{
    public function index(){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $username=$user->getusername($userid);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            $project=new \app\api\controller\Project();
            $waitinglist=$project->getuserwaitingprojectbyuserid($userid);
            $workinglist=$project->getuserworkingprojectbyuserid($userid);
            $this->assign("waitinglist",$waitinglist);
            $this->assign('workinglist',$workinglist);
            return $this->fetch('index');
        }
        return $this->error('404 未知接包人员');
    }
}