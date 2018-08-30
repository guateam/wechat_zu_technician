<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 主页
 * 部分实装
 */
class Index extends Controller
{
    public function index()
    {
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $user=new \app\api\controller\Contractors();
            $waitinglist=$user->waitinglist($_COOKIE["contractorid"]);
            $project=new \app\api\controller\Project();
            $projectlist=$project->getprojectlist($_COOKIE["contractorid"]);
            $userlist=$project->getallprojectuserlist($userid);
            $this->assign('userlist',$userlist);
            $this->assign('projectlist',$projectlist);
            $this->assign('waitinglist',$waitinglist);
            $this->assign('username',$user->getusername($userid));
            return $this->fetch();
        }
        return $this->error('404 未知发包人员');
    }
    /**
     * 获取项目比例
     */
    public function getprojectpan($userid){
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($userid);
        $project=new \app\api\controller\Project();
        $data=$project->getprojectpan($userid);
        if($data){
            $back=["data"=>$data,"status"=>1];
            return json($back);
        }
    }
    
    /**
     * 获取聊天框
     * 2018-3-7 张煜
     */
    public function chatingbox($id){
        $user=new \app\api\controller\Contractors();
        $data=$user->chatingbox($id);
        if($data){
            return json(['data'=>$data,'status'=>1]);
        }
        return json(['status'=>0]);
    }

    public function getlog(){
        $log=new \app\api\controller\Log();
        $data=$log->getAllLog();
        if($data){
            return json(['status'=>1,'data'=>$data]);
        }
        return json(['status'=>0]);
    }
    public function send($id,$status,$note){
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($id);
        $username='发包员 '.$user->getusername($userid);
        $chatingbox= new \app\api\controller\ChatingBox();
        $time=date('Y-m-d H:i');
        $chatingbox->send($username,$status,$time,$note,0);
        return json(['status'=>1]);
    }
}
