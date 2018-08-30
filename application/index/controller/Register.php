<?php
namespace app\index\controller;
use think\Controller;
//use think\db;
/**
 * 注册界面
 */
class Register extends Controller
{
    public function index()
    {
        return $this->fetch('registeruser');
    }
    

}