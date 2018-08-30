<?php
namespace app\index\controller;
use think\Controller;
//use think\db;
/**
 * 忘记密码界面
 * 界面已实现
 * 功能未实现
 */
class Forget extends Controller
{
    public function index()
    {
        return $this->fetch('forget');
    }
}