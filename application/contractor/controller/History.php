<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 历史记录页面
 * 已实装
 */
class History extends Controller
{
    public function index()
    {
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $project=new \app\api\controller\Project();
            $data=$project->gethistoryproject();
            $this->assign("data",$data);
            return $this->fetch('history');
        }
        return $this->error('404 未知发包人员');
    }
}
