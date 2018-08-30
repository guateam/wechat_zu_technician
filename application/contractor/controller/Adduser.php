<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 为项目添加人员列表页面
 * 已实装
 */
class Adduser extends Controller
{
    public function index()
    {
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $project=new \app\api\controller\Project();
            $waitinglist=$project->getwaitinguserproject($userid);
            $workinglist=$project->getworkingproject($userid);
            $this->assign("waitinglist",$waitinglist);
            $this->assign("workinglist",$workinglist);
            return $this->fetch('adduser');
        }
        return $this->error('404 未知发包人员');
    }
}
