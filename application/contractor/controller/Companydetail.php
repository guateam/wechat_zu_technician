<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 公司详情界面
 * 部分实装
 * 不想改，等alpha版
 * 2018-3-4 张煜
 */
class Companydetail extends Controller
{
    public function index($id)
    {
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $this->assign('username',$user->getusername($userid));
            $company=new \app\api\controller\Companies();
            $data=$company->getcompanydata($id);
            if($data){
                $this->assign('data',$data);
                $user=new \app\api\controller\Users();
                $userlist=$user->getuserlistbycompany($id);
                $this->assign('userlist',$userlist);
                return $this->fetch('companydetail');
            }else{
                return $this->error("404 未知公司");
            }
        }
        return $this->error('404 未知发包人员');
    }
}
