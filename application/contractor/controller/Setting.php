<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 设置页面
 * 未实装
 * 页面部分设计
 */
class Setting extends Controller
{
    public function index()
    {
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $data=$user->getuserdetail($userid);
            $this->assign('data',$data);
            return $this->fetch();
        }
        return $this->error('404 未知发包人员');
    }

    public function change($id,$name,$gender,$birthday,$nation,$phonenumber,$emergencyname,$emergencyphone,$password){
        $user=new \app\api\controller\Contractors();
        $user->change($id,$name,$gender,$birthday,$nation,$phonenumber,$emergencyname,$emergencyphone,$password);
        return json(['status'=>1]);
    }
}
