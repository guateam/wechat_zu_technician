<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 正在进行项目页面
 * 已实装
 */
class Workingprojectlist extends Controller
{
    public function index()
    {
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $project=new \app\api\controller\Project();
            $workinglist=$project->getworkingproject($userid);
            $this->assign('workinglist',$workinglist);
            $this->assign('projectlistname','正在运行项目');
            return $this->fetch('workingprojectlist');
        }
        return $this->error('404 未知发包人员');
    }
    public function delaylist(){
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $project=new \app\api\controller\Project();
            $workinglist=$project->getdelayproject($userid);
            $this->assign('workinglist',$workinglist);
            $this->assign('projectlistname','延期项目列表');
            return $this->fetch('workingprojectlist');
        }
        return $this->error('404 未知发包人员');
    }
    public function ontimelist(){
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $project=new \app\api\controller\Project();
            $workinglist=$project->getontimeproject($userid);
            $this->assign('workinglist',$workinglist);
            $this->assign('projectlistname','进度正常项目列表');
            return $this->fetch('workingprojectlist');
        }
        return $this->error('404 未知发包人员');
    }
    public function busylist(){
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $project=new \app\api\controller\Project();
            $workinglist=$project->getbusyproject($userid);
            $this->assign('workinglist',$workinglist);
            $this->assign('projectlistname','可能延期项目列表');
            return $this->fetch('workingprojectlist');
        }
        return $this->error('404 未知发包人员');
    }
}
