<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * email的详情页面
 * 实装了内容页面，下半部分的回复部分未实装
 * 2018-3-4 张煜
 */
class Email extends Controller
{
    public function index($id)
    {
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $email= new \app\api\controller\Email();
            $data=$email->getemaildetail($id);
            if($data){
                $email->setRead($id,'c'.$userid.',');
                $this->assign('data',$data);
                return $this->fetch('email');
            }else{
                return $this->error('404 未知的邮件页面');
            }
        }
        return $this->error('404 未知发包人员');
    }
    public function auto($term){
        $email=new \app\api\controller\Email();
        $data=$email->getuserlist($term);
        return json($data);
    }
}
