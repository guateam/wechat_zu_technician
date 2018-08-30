<?php
namespace app\user\controller;
use think\Controller;
//use think\db;
/**
 * 公司详情界面
 * 照搬contractor的内容，要改
 * 2018-3-4 张煜
 */
class Companydetail extends Controller
{
    public function index($id){
        $user=new \app\api\controller\Users();
        $userid=$user->checkuser($_COOKIE["userid"]);
        if($userid){
            $username=$user->getusername($userid);
            $this->assign('username',$username);
            $this->assign('userid',$userid);
            $company=new \app\api\controller\Companies();
            $data=$company->getcompanydata($id);
            if($data){
                $this->assign('data',$data);
                $userlist=$user->getuserlistbycompany($id);
                $this->assign('userlist',$userlist);
                return $this->fetch('companydetail');
            }else{
                return $this->error("404 未知公司");
            }
        }
        return $this->error('404 未知接包人员');
    }
}