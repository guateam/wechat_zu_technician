<?php
namespace app\user\controller;
use think\Controller;
//use think\db;
/**
 * 为项目添加人员列表页面
 * 已实装
 */
class Uploadresource extends Controller
{
    public function index($id)
    {
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $this->assign('userid',$userid);
            $this->assign('username',$user->getusername($userid));
            $this->assign('projectid',$id);
            return $this->fetch('uploadresource');
        }
        return $this->error('404 未知发包人员');
    }
    public function change($id){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $resource=new \app\api\controller\Resource();
            $data=$resource->getresource($id);
            if($data){
                $this->assign('data',$data);
                $this->assign('userid',$userid);
                $this->assign('username',$user->getusername($userid));
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
    public function addresource($id,$name,$type,$safetygrade,$address,$uploader){
        $resource=new \app\api\controller\Resource();
        $project = new \app\api\controller\Project();
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($uploader);
        $resourceid=$resource->add($name,'u'.$userid,$safetygrade,$type,$address,'等待审核');
        $project->addprojectresultid($id,$resourceid);
        $email=new \app\api\controller\Email();
        $email->send('成果上传','u'.$userid,',c'.$project->getContractorID($id).',','普通','成果资源'.$name.'上传，等待审核：<a class="btn btn-primary" href="/tp5/public/index.php/contractor/resourcecheck/resultcheck/id/'.$resourceid.'">成果审核</a>',1,'');
        return json(['status'=>1]);
    }
    public function changeresource($id,$name,$type,$safetygrade,$address,$uploader){
        $resource=new \app\api\controller\Resource();
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($uploader);
        $resourceid=$resource->edit($id,$name,'u'.$userid,$safetygrade,$type,$address,'等待审核');
        $email=new \app\api\controller\Email();
        $email->send('成果上传','u'.$userid,',c'.$project->getContractorID($id).',','普通','成果资源'.$name.'上传，等待审核：<a class="btn btn-primary" href="/tp5/public/index.php/contractor/resourcecheck/resultcheck/id/'.$resourceid.'">成果审核</a>',1,'');
        return json(['status'=>1]);
    }
}
