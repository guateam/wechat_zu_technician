<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 用户详细页面
 * 部分实装
 * 不想动
 */
class Userdetail extends Controller
{
    public function index($id)
    {   
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $user=new \app\api\controller\Users();
            $data=$user->getbasicuserbyid($id);
            if($data){
                switch($data['safetygrade']){
                    case 2:
                        $data['safetygrade']='高';
                        break;
                    case 1:
                        $data['safetygrade']='中';
                        break;
                    case 2:
                        $data['safetygrade']='低';
                    break;
                }
                $company= new \app\api\controller\Companies();
                $companydata=$company->getcompanydata($data['companyid']);
                switch($companydata['safetygrade']){
                    case 2:
                        $companydata['safetygrade']='高';
                        break;
                    case 1:
                        $companydata['safetygrade']='中';
                        break;
                    case 2:
                        $companydata['safetygrade']='低';
                    break;
                }
                $project=new \app\api\controller\Project();
                $projectdata=$project->getuserreadyproject($id);
                $this->assign("data",$data);
                $this->assign("id",$id);
                $this->assign('company',$companydata);
                $this->assign('project',$projectdata);
                return $this->fetch('userdetail');
            }else{
                return $this->error('404 未知用户');
            }
        }
        return $this->error('404 未知发包人员');
    }

    public function getradarmap($id){
        $user=new \app\api\controller\Users();
        $data=$user->getradarmap($id);
        if($data){
            $back=["data"=>$data,"status"=>1];
            return json($back);
        }else{
            return json(['status'=>0]);
        }
    }
}
