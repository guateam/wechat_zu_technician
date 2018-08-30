<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 项目新建页面
 * 未实装
 * 不知道怎么实现
 * 2018-3-4 张煜
 */
class Add extends Controller
{
    public function index()
    {
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $project=new \app\api\controller\Project();
            $projectlist=$project->getallprojectlist($userid);
            $this->assign('projectlist',$projectlist);
            $this->assign('username',$user->getusername($userid));
            return $this->fetch('add');
        }
        return $this->error('404 未知发包人员');
    }
    public function addtree($id){
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $this->assign('projectid',$id);
            return $this->fetch('addtree');
        }
        return $this->error('404 未知发包人员');
    }
}
