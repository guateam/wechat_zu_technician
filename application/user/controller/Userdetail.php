<?php
namespace app\user\controller;
use think\Controller;
//use think\db;
/**
 * 现在是照搬的contractor的用户详情
 * 以后要改
 * 2018-3-4 张煜
 */
class Userdetail extends Controller
{
    public function index($id)
    {
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $username=$user->getusername($userid);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            $user=new \app\api\controller\Users();
            $data=$user->getbasicuserbyid($id);
            if($data){
                $company= new \app\api\controller\Companies();
                $companydata=$company->getcompanydata($data['companyid']);
                $this->assign("data",$data);
                $this->assign("id",$id);
                $this->assign('company',$companydata);
                $project=new \app\api\controller\Project();
                $projectdata=$project->getuserreadyproject($id);
                $this->assign('project',$projectdata);
                return $this->fetch('userdetail');
            }else{
                return $this->error('404 未知用户');
            }
        }
        return $this->error('404 未知接包人员');
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