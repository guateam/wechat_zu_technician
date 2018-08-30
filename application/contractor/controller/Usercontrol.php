<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 用户管理界面
 * 未实装
 * 界面未设计
 */
class Usercontrol extends Controller
{
    public function index()
    {
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $user=new \app\api\controller\Users();
            $userlist=$user->getalluserlist();
            $company=new \app\api\controller\Companies();
            $companylist=$company->getallcompanieslist();
            $this->assign('userlist',$userlist);
            $this->assign('companylist',$companylist);
            return $this->fetch('usercontrol');
        }
        return $this->error('404 未知发包人员');
    }
}
