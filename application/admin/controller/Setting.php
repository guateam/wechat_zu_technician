<?php
namespace app\admin\controller;

use think\Controller;

class Setting extends Controller
{
    public function index()
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $this->assign('username', $user->getusername($userid));
            $data = $user->getuserdetail($userid);
            $this->assign('data', $data);
            return $this->fetch();
        }
        return $this->error('404 未知管理员');
    }
    public function change($id, $name, $phonenumber, $password)
    {
        $user = new \app\api\controller\Administractors();
        $user->change($id, $name, $phonenumber, $password);
        return json(['status' => 1]);
    }
}
