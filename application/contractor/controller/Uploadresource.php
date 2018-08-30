<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 为项目添加人员列表页面
 * 已实装
 */
class Uploadresource extends Controller
{
    public function index()
    {
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            return $this->fetch('uploadresource');
        }
        return $this->error('404 未知发包人员');
    }
    public function changeindex($id){
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $resource= new \app\api\controller\Resource();
            $resourcedata=$resource->getresource($id);
            $this->assign('username',$user->getusername($userid));
            if($resourcedata){
                $this->assign('resource',$resourcedata);
                $this->assign('id',$id);
                return $this->fetch('changeresource');
            }
            return $this->error('404 未知资源');
        }
        return $this->error('404 未知发包人员');
    }
    public function upload(){
        $file = request()->file('file');
        $address='';
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads/resource');
            if($info){
                // 成功上传后 获取上传
                $list=explode('\\',$info->getSaveName());
                $address=$list[0].'/'.$list[1];
                
                return json(['status'=>1,'address'=>$address]);
            }else{
                return json(['status'=>0]);
            }
        }
        
    }
    public function addresource($name,$type,$safetygrade,$address,$uploader,$state){
        $resource=new \app\api\controller\Resource();
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($uploader);
        $resource->add($name,'c'.$userid,$safetygrade,$type,$address,$state);
        return json(['status'=>1]);
    }
    public function changeresource($id,$name,$type,$safetygrade,$address,$uploader,$state){
        $resource=new \app\api\controller\Resource();
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($uploader);
        $resource->edit($id,$name,'c'.$userid,$safetygrade,$type,$address,$state);
        return json(['status'=>1]);
    }
    public function auto($term){
        $user=new \app\api\controller\Contractors();
        $data=$user->autoc($term);
        $user=new \app\api\controller\Users();
        $data=\array_merge($data,$user->autoc($term));
        return json($data);
    }
}
