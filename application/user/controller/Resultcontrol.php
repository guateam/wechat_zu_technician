<?php
namespace app\user\controller;
use think\Controller;
//use think\db;
/**
 * 选择上传资源的页面
 * 2018-3-25 张煜
 */
class Resultcontrol extends Controller
{
    public function index($id){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $project=new \app\api\controller\Project();
            $data=$project->getresultcontrollist($id,$userid);
            $username=$user->getusername($userid);
            $this->assign('data',$data);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            $this->assign('id',$id);
            return $this->fetch('resultcontrol');
        }
        return $this->error('404 未知接包人员');
    }
}