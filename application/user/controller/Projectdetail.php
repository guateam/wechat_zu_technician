<?php
namespace app\user\controller;
use think\Controller;
//use think\db;
/**
 * 项目详情界面
 * 部分实装
 * 早期文件
 * 需要更改
 */
class Projectdetail extends Controller
{
    public function index($id)
    {   
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $username=$user->getusername($userid);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            //project的基础数据
            $project=new \app\api\controller\Project();
            if($project->checkuser($userid,$id)){
                $data=$project->getbasicprojectbyid($id);
                if($data){
                    $waitinglist=$project->getprojectwatinglist($id);
                    $resourcelist=$project->getprojectresource($id,$userid);
                    $log=new \app\api\controller\Log();
                    $logflag=$log->getset($userid,$id);
                    $this->assign('resourcelist',$resourcelist);
                    $this->assign('waitinglist',$waitinglist);
                    $this->assign('project',$data);
                    $this->assign('id',$id);
                    $this->assign('log',$logflag);
                    return $this->fetch('projectdetail');
                }else{
                    $this->error('不存在的项目');
                }
            }
            return $this->error('403 您不在此项目中');
        }
        return $this->error('404 未知接包人员');
    }
    public function waitinglist($id){
        //获取需要
        $project=new \app\api\controller\Project();
        
        $data=$project->getprojectwatinglist($id);
        if($data){
            $back=['data'=>$data,'status'=>1];
            return json($back);
        }else{
            return json(['status'=>0]);
        }
    }
    public function structuredata($id){
        $project=new \app\api\controller\Project();
        
        $data=$project->getprojectstructuretree($id);
        if($data){
            $back=['data'=>[$data],'status'=>1];
            return json($back);
        }else{
            return json(['status'=>0]);
        }
        
    }
    public function waterfall($id){
        $project=new \app\api\controller\Project();
        
        $data=$project->getprojectwaterfall($id);
        if($data){
            $back=['data'=>$data,'status'=>1];
            return json($back);
        }else{
            return json(['status'=>1,'data'=>[[
                'name'=>"没有数据",
                'starttime'=>"",
                'endtime'=>"",
                'status'=>"",
                'id'=>$id
            ]]]);
        }
    }
    public function resourcelist($id){
        $project=new \app\api\controller\Project();
        
        $data=$project->getprojectresource($id,1);
        if($data){
            $back=['data'=>$data,'status'=>1];
            return json($back);
        }else{
            return json(['status'=>0]);
        }
    }
    public function chartingboxdata($id){
        $box=new \app\api\controller\ChatingBox();
        $project=new \app\api\controller\Project();
        
        $data=$box->read($id);
        $data=\array_merge($box->read(0),$data);
        if($data){
            $back=['data'=>$data,'status'=>1];
            return json($back);
        }else{
            return json(['status'=>0]);
        }
    }
    public function send($id,$status,$note,$projectid){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($id);
        $username='接包员 '.$user->getusername($userid);
        $chatingbox= new \app\api\controller\ChatingBox();
        $time=date('Y-m-d H:i');
        $chatingbox->send($username,$status,$time,$note,$projectid);
        return json(['status'=>1]);
    }
    public function stop($id,$cookie){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($cookie);
        if($userid){
            $log=new \app\api\controller\Log();
            $worktime=$log->stoplog($id,$userid,$user->getusername($userid));
            $user->setOnlineState($userid,1);
            $user->addworktime($userid,$worktime);
            return json(['status'=>1]);
        }
    }
}