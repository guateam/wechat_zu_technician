<?php
namespace app\admin\controller;

use think\Controller;

class Sendemail extends Controller
{
    public function index()
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $this->assign('username', $user->getusername($userid));
            return $this->fetch('sendemail');
        }
        return $this->error('404 未知管理员');
    }
    public function send($name, $poster, $note)
    {
        $email = new \app\api\controller\Email();
        $email->sendall($name, $poster, $note);
        return json(['status' => 1]);
    }
}
