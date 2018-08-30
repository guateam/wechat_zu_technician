<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 资源查看页面
 * 已实装
 */
class Resourcecheck extends Controller
{
    public function index($id)
    {   
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $resource=new \app\api\controller\Resource();
            $data=$resource->getresource($id);
            if($data){
                $this->assign('data',$data);
                $this->assign('username',$user->getusername($userid));
                $this->assign('id',$id);
                return $this->fetch('resourcecheck');
            }
            return $this->error('404 未知资源');
        }
        return $this->error('404 未知发包人员');
    }
    public function resultcheck($id){
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $resource=new \app\api\controller\Resource();
            $data=$resource->getresource($id);
            if($data){
                $this->assign('data',$data);
                $this->assign('username',$user->getusername($userid));
                $this->assign('id',$id);
                return $this->fetch('resultcheck');
            }
            return $this->error('404 未知资源');
        }
        return $this->error('404 未知发包人员');
    }
    public function resourceaccept($oldid,$newid,$logid){
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $resource=new \app\api\controller\Resource();
            $old=$resource->getresource($oldid);
            $new=$resource->getresource($newid);
            $this->assign('old',$old);
            $this->assign('new',$new);
            $this->assign('logid',$logid);
            return $this->fetch('resourceaccept');
        }
        return $this->error('404 未知发包人员');
    }
    public function acceptresult($id,$cookie){
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($cookie);
        if($userid){
            $resource=new \app\api\controller\Resource();
            $resource->acceptresult($id);
            return json(['status'=>1]);
        }
    }
    public function returnresult($id,$cookie){
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($cookie);
        if($userid){
            $resource=new \app\api\controller\Resource();
            $resource->returnresult($id);
            return json(['status'=>1]);
        }
    }
}
