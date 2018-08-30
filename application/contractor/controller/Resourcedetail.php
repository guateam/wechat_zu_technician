<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 资源详情页面
 * 未实装
 * 页面未设计
 */
class Resourcedetail extends Controller
{
    public function index()
    {
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            return $this->fetch('resourcedetail');
        }
        return $this->error('404 未知发包人员');
    }
}
