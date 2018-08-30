<?php
namespace app\index\controller;
use think\Controller;
//use think\db;
/**
 * login界面
 * 界面实现
 * 功能未实现
 */
class Login extends Controller
{
    public function index()
    {
        
        return $this->fetch('login');
    }
}