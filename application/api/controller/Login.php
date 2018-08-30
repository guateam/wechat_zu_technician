<?php
namespace app\api\controller;
use think\Controller;
use \app\api\controller\Users;
use \app\api\controller\Contractors;
use \app\api\controller\Administractors;
/**
 * 用于登陆界面
 * 自动识别人员并跳转
 * 2018-3-4 张煜
 */
class Login extends Controller{
    /**
     * 自动通过手机号识别登陆
     * 实装
     * 2018-3-4 张煜
     * 2018-3-12袁宜照 将人脸检测分离到了其他地方
     */
    public function login($phonenumber,$password){
        $user=new Users();
        $contractor=new Contractors();
        $administractor=new Administractors();
        if($data=$user->loginbyphonenumber($phonenumber,$password)){
            return $data;
        }else if($data=$contractor->loginbyphonenumber($phonenumber,$password)){
            return $data;
        }else if($data=$administractor->loginbyphonenumber($phonenumber,$password)){
            return $data;
        }else{
            return json(["status"=>-2]);
        }
    }
    /**
     * 人脸检测
     * 2018-3-12袁宜照
     */
    public function facecheck($phonenumber,$faceinform)
    {
        $user=new Users();
        $contractor=new Contractors();
        $administractor=new Administractors();
        if($data=$user->facecheck($phonenumber,$faceinform)){
            return $data;
        }else if($data=$contractor->facecheck($phonenumber,$faceinform)){
            return $data;
        }else if($data=$administractor->facecheck($phonenumber,$faceinform)){
            return $data;
        }else{
            return json(["status"=>-2]);
        }
    }
    /**
     * 忘记密码方法
     * 未实装
     * 2018-3-4 张煜
     * 修改 2018-4-4 袁宜照
     */
    public function forget($phonenumber,$password){
        $user=new Users();
        $contractor=new Contractors();
        $administractor=new Administractors();
        if($data=$user->newpassword($phonenumber,$password)){
            return $data;
        }else if($data=$contractor->newpassword($phonenumber,$password)){
            return $data;
        }else if ($data=$administractor->newpassword($phonenumber,$password)){
            return $data;
        }else{
            return json(["status"=>-2]);
        }
    }

    /**
     * 注册方法
     * 未实装
     * 2018-3-4 张煜
     */
    public function register(){
        
    }
    /**
     * 登出方法
     * 2018-3-24 张煜
     */
    public function logout($userid,$type){
        switch ($type) {
            case 0:
                $user=new Administractors();
                break;
            case 1:
                $user=new Contractors();
                break;
            case 2:
                $user=new Users();
                break;
            default:
                return json(['status'=>0]);
                break;
        }
        $user->logout($userid);
        return json(['status'=>1]);
    }
}