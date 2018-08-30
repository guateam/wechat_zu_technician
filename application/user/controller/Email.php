<?php
namespace app\user\controller;
use think\Controller;
//use think\db;
/**
 * email详情界面，照搬contracotor的
 * 部分实装
 * 201-3-4 张煜
 */
class Email extends Controller
{
    public function index($id)
    {
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $username=$user->getusername($userid);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            $email= new \app\api\controller\Email();
            $data=$email->getemaildetail($id);
            if($data){
                $email->setRead($id,'u'.$userid.',');
                $this->assign('data',$data);
                return $this->fetch('email');
            }else{
                return $this->error('404 未知的邮件页面');
            }
        }
        return $this->error('404 未知接包人员');
    }
    public function auto($term){
        $email=new \app\api\controller\Email();
        $data=$email->getuserlist($term);
        return json($data);
    }
}