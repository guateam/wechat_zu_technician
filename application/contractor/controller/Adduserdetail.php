<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 项目人员添加的详细页面
 * 未实装
 * 界面未设计
 * 
 */
class Adduserdetail extends Controller
{
    public function index($id)
    {
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $project=new \app\api\controller\Project();
            $data=$project->getbasicprojectbyid($id);
            if($data){
                $user=new \app\api\controller\Users();
                $level=0;
                if($data['safetygrade']=='高'){
                    $level=2;
                }else if($data['safetygrade']=='中'){
                    $level=1;
                }
                $readylist=$project->getprojectreadylist($id);
                $userlist=$user->getuserlist($level,$id);
                $this->assign('readylist',$readylist);
                $this->assign('userlist',$userlist);
                $this->assign('project',$data);
                $this->assign('id',$id);
                return $this->fetch('adduserdetail');
            }
            return $this->error('404 未知项目');
        }
        return $this->error('404 未知发包人员');
    }

    public function set($id,$userid){
        $project=new \app\api\controller\Project();
        $back=$project->addprojectuserid($id,$userid);
        return json(['status'=>$back]);
    }
    public function deleteuser($id,$userid){
        $project=new \app\api\controller\Project();
        $back=$project->deleteprojectuserid($id,$userid);
        return json(['status'=>$back]);
    }
}
