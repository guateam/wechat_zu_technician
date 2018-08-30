<?php
namespace app\user\controller;
use think\Controller;
//use think\db;
/**
 * 以后会整合到设置里
 * 不建议
 */
class Resourcecheck extends Controller
{
    public function index($id){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $resource=new \app\api\controller\Resource();
            $data=$resource->getresource($id);
            if($data){
                $this->assign('data',$data);
                $this->assign('username',$user->getusername($userid));
                $this->assign('userid',$userid);
                $this->assign('id',$id);
                return $this->fetch('resourcecheck');
            }
            return $this->error('404 未知资源');

            return $this->fetch('resourcecheck');
        }
        return $this->error('404 未知接包人员');
    }
}