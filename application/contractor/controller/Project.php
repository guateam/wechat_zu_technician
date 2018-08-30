<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 项目详细界面
 * 部分实装
 */
class Project extends Controller
{
    public function index($id)
    {
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $project=new \app\api\controller\Project();
            $data=$project->getbasicprojectbyid($id);

            if($data){
                $this->assign('project',$data);
                $children=$project->getchildrenproject($data['id']);
                $email=new \app\api\controller\Email();
                $log=new \app\api\controller\Log();
                $tablist=[];
                $childlist=[];
                if($children){
                    foreach ($children as $key => $value) {
                        if($key==0){
                            $item=[
                                "tabtype"=>"home-tab",
                                'id'=>$value['id'],
                                'name'=>$value['name'],
                                'starttime'=>$value['starttime'],
                                'endtime'=>$value['endtime'],
                                'user'=>$value['user'],
                                'safetygrade'=>$value['safetygrade'],
                                'plan'=>$value['plan'],
                                'color'=>$value['color'],
                                'class'=>'active in',
                                'bug'=>$email->getbuglistbyid($value['id'],$userid),
                                'result'=>$project->getresultlist($value['id']),
                                'log'=>$log->getprojectlog($value['id'])
                            ];
                            array_push($childlist,$item);
                            $item=["id"=>$value['id'],"name"=>$value['name'],'type'=>'home-tab','class'=>'active'];
                            array_push($tablist,$item);
                        }else{
                            $item=[
                                "tabtype"=>"profile-tab",
                                'id'=>$value['id'],
                                'name'=>$value['name'],
                                'starttime'=>$value['starttime'],
                                'endtime'=>$value['endtime'],
                                'user'=>$value['user'],
                                'safetygrade'=>$value['safetygrade'],
                                'plan'=>$value['plan'],
                                'color'=>$value['color'],
                                'class'=>'',
                                'bug'=>$email->getbuglistbyid($value['id'],$userid),
                                'result'=>$project->getresultlist($value['id']),
                                'log'=>$log->getprojectlog($value['id'])
                            ];
                            array_push($childlist,$item);
                            $item=["id"=>$value['id'],"name"=>$value['name'],'type'=>'profile-tab','class'=>''];
                            array_push($tablist,$item);
                        }
               
                    }
                }
                $result=$project->getresultlist($id);
                $resource=$project->getprojectresource($id,$userid,1);
                $this->assign('resultlist',$result);
                $this->assign('resource',$resource);
                $this->assign('tablist',$tablist);
                $this->assign('childlist',$childlist);
                $this->assign('id',$id);
            }else{
                $this->error('不存在的项目');
            }
            return $this->fetch('project');
        }
        return $this->error('404 未知发包人员');
    }

    public function getprojectstatus($id){//调用project->getspeederdata返回仪表盘的数据
        $project=new \app\api\controller\Project();
        $data=$project->getspeederdata($id);
        if($data){
            $back=["data"=>$data,"status"=>1];
            return json($back);
        }else{
            return ['status'=>1];
        }
    }

    public function chartingboxdata($id){
        $box=new \app\api\controller\ChatingBox();
        $project=new \app\api\controller\Project();
        
        $data=$box->read($id);
        if($data){
            $back=['data'=>$data,'status'=>1];
            return json($back);
        }else{
            return json(['status'=>0]);
        }
    }

    public function send($id,$status,$note,$projectid){
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($id);
        $username='发包员 '.$user->getusername($userid);
        $chatingbox= new \app\api\controller\ChatingBox();
        $time=date('Y-m-d H:i');
        $chatingbox->send($username,$status,$time,$note,$projectid);
        return json(['status'=>1]);
    }

    public function getallprojectchildrenlist($id){
        $project=new \app\api\controller\Project();
        $data=$project->getallprojectchildrenlist($id);
        if($data){
            return json(['data'=>$data,'status'=>1]);
        }
        return json(['status'=>0]);
    }

    public function getuserlist($id){
        $project=new \app\api\controller\Project();
        $data=$project->getprojectuserlist($id);
        if($data){
            return json(['data'=>$data,'status'=>1]);
        }
        return json(['status'=>0]);
    }
}