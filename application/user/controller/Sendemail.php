<?php
namespace app\user\controller;
use think\Controller;
//use think\db;
/**
 * 邮件的发送页面
 * 已实装
 * 2018-3-4 张煜
 */
class Sendemail extends Controller
{
    public function index()
    {
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $username=$user->getusername($userid);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            $email=[
                'name'=>'',
                'recipients'=>'',
                'type'=>'普通',
                'note'=>''
            ];
            $this->assign('email',$email);
            return $this->fetch('sendemail');
        }
        return $this->error('404 未知接包人员');
    }

    public function sendbug($id){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $username=$user->getusername($userid);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            $project=new \app\api\controller\Project();
            $recipients=$project->getcontractorid($id);
            $email=[
                'name'=>'Bug：',
                'recipients'=>',c'.$recipients.',',
                'type'=>'bug',
                'note'=>'@'.$id.'@##'
            ];
            $this->assign('email',$email);
            return $this->fetch('sendemail');
        }
        return $this->error('404 未知接包人员');
    }

    public function sendresource($id){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $username=$user->getusername($userid);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            $project=new \app\api\controller\Project();
            $recipients=$project->getcontractorid($id);
            $email=[
                'name'=>'资源：',
                'recipients'=>',c'.$recipients.',',
                'type'=>'资源',
                'note'=>'@'.$id.'@##'
            ];
            $this->assign('email',$email);
            return $this->fetch('sendemail');
        }
        return $this->error('404 未知接包人员');
    }
    
    public function sendtocontractor($id){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $username=$user->getusername($userid);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            $project=new \app\api\controller\Project();
            $recipients=$project->getcontractorid($id);
            $email=[
                'name'=>'',
                'recipients'=>',c'.$recipients.',',
                'type'=>'',
                'note'=>''
            ];
            $this->assign('email',$email);
            return $this->fetch('sendemail');
        }
        return $this->error('404 未知接包人员');
    }
    public function auto($term){
        $email=new \app\api\controller\Email();
        $data=$email->getuserlist($term);
        return json($data);
    }
}
