<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 资源添加列表页面
 * 已实装
 */
class Addresource extends Controller
{
    public function index()
    {   
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $project=new \app\api\controller\Project();
            $waitinglist=$project->getwaitingresourceproject($userid);
            $workinglist=$project->getworkingproject($userid);
            $this->assign("waitinglist",$waitinglist);
            $this->assign("workinglist",$workinglist);
            return $this->fetch('addresource');
        }
        return $this->error('404 未知发包人员');
    }
}
