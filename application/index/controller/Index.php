<?php
namespace app\index\controller;
use think\Controller;
//use think\db;
/**
 * 提供各页面入口
 * 之后直接跳转login界面
 */
class Index extends Controller
{
    public function index()
    {
        return $this->success('请先登陆','index/login/index');
    }
}
