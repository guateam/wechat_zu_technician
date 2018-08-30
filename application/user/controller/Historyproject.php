<?php
namespace app\user\controller;
use think\Controller;
//use think\db;
/**
 * 历史记录详细界面
 * 照搬contractor
 * 实装
 */
class Historyproject extends Controller
{
    public function index($id){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $username=$user->getusername($userid);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            $project=new \app\api\controller\Project();
            $data=$project->getbasicprojectbyid($id);
            if($data){
                $this->assign("project",$data);
                $children=$project->getchildrenproject($id);
                $rating=$project->gethistoryprojectrating($id);
                $this->assign('rating',$rating);
                $this->assign("childrenlist",$children);
                $this->assign('id',$id);
                return $this->fetch('historyproject');
            }
            return $this->error("404 未知的历史项目");
        }
        return $this->error('404 未知接包人员');
    }

    public function getradarmap($id){
        $project=new \app\api\controller\Project();
        $data=$project->getradarmap($id);
        if($data){
            $back=["data"=>$data,"status"=>1];
            return json($back);
        }else{
            return json(["status"=>0]);
        }
    }
}