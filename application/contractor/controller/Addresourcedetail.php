<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 项目资源添加的详细页面
 * 未实装
 * 界面未设计
 * 2018-3-4 张煜
 */
class Addresourcedetail extends Controller
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
                $resource=new \app\api\controller\Resource();
                $level=0;
                if($data['safetygrade']=='高'){
                    $level=2;
                }else if($data['safetygrade']=='中'){
                    $level=1;
                }
                $readylist=$project->getprojectresourcereadylist($id);
                $waitinglist=$resource->getresourcelist($level,$id);
                $this->assign('readylist',$readylist);
                $this->assign('waitinglist',$waitinglist);
                $this->assign('project',$data);
                $this->assign('id',$id);
                return $this->fetch('addresourcedetail');
            }
            return $this->error('404 未知项目');
        }
        return $this->error('404 未知发包人员');
    }

    public function set($id,$resourcelist=[]){
        $project=new \app\api\controller\Project();
        $back=$project->addprojectresourceid($id,$resourcelist);
        return json(['status'=>$back]);
    }
    public function deleteresource($id,$resourceid){
        $project=new \app\api\controller\Project();
        $back=$project->deleteprojectresourceid($id,$resourceid);
        return json(['status'=>$back]);
    }
}
