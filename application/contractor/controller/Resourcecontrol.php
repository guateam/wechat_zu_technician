<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 资源管理
 * 未实装
 */
class Resourcecontrol extends Controller
{
    public function index()
    {
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $resource=new \app\api\controller\Resource();
            $data=$resource->getallresourcelist($userid);
            $this->assign('data',$data);
            return $this->fetch('resourcecontrol');
        }
        return $this->error('404 未知发包人员');
    }

    public function delete($idlist){
        $resource=new \app\api\controller\Resource();
        $resource->deletelist($idlist);
        return json(['status'=>1]);
    }
}
