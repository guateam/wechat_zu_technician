<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 项目完成打分页面
 * 2018-3-21 张煜
 */
class Projectrank extends Controller
{
    public function index($id)
    {   
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $project =new \app\api\controller\Project();
            $data=$project->getbasicprojectbyid($id);
            if($data){
                $this->assign('project',$data);
                $this->assign('id',$id);
                $this->assign('child',[]);
                return $this->fetch('projectrank');
            }
            return $this->error('404 未知项目');
        }
        return $this->error('404 未知发包人员');
    }
    public function setprojectrank($id,$totalrank,$userrank,$degree,$speed){
        $project=new \app\api\controller\Project();
        $project->setprojectrank($id,$totalrank,$userrank,$degree,$speed);
        return json(['status'=>1]);
    }
}
