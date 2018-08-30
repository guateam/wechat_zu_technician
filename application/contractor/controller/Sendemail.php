<?php
namespace app\contractor\controller;
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
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            return $this->fetch('sendemail');
        }
        return $this->error('404 未知发包人员');
    }
    public function returnresult($id){
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $resource=new \app\api\controller\Resource();
            $data=$resource->getresource($id);
            if($data){
                $this->assign('data',$data);
                $this->assign('id',$id);
                $this->assign('username',$user->getusername($userid));
                return $this->fetch('returnemail');
            }
            return $this->error('404 未知资源');
        }
        return $this->error('404 未知发包人员');
    }
    public function auto($term){
        $email=new \app\api\controller\Email();
        $data=$email->getuserlist($term);
        return json($data);
    }
}
