<?php
namespace app\admin\controller;
use think\Controller;
//use think\db;
/**
 * 旧管理员界面
 * 已弃用
 * 新内容见 宴鹏 
 * 不建议
 */
class Admin extends Controller
{   
    public function _initialize(){
        \think\Log::init([
            'type'  =>  "File",
            'path'  =>  APP_PATH.'logs/'
        ]);
        \think\Log::record('测试日志信息');
    }
    public function index()
    {
        $sysos = $_SERVER["SERVER_SOFTWARE"];      //获取服务器标识的字串
        $sysversion = PHP_VERSION;                   //获取PHP服务器版本
        //以下两条代码连接MySQL数据库并获取MySQL数据库版本信息
        $connect=mysqli_connect("localhost", "root", "zhangyuk");
        $mysqlinfo = mysqli_get_server_info($connect);
        //从服务器中获取GD库的信息
        if(function_exists("gd_info")){                  
        $gd = gd_info();
        $gdinfo = $gd['GD Version'];
        }else {
        $gdinfo = "未知";
        }
        //从GD库中查看是否支持FreeType字体
        $freetype = $gd["FreeType Support"] ? "支持" : "不支持";
        //从PHP配置文件中获得是否可以远程文件获取
        $allowurl= ini_get("allow_url_fopen") ? "支持" : "不支持";
        //从PHP配置文件中获得最大上传限制
        $max_upload = ini_get("file_uploads") ? ini_get("upload_max_filesize") : "Disabled";
        //从PHP配置文件中获得脚本的最大执行时间
        $max_ex_time= ini_get("max_execution_time")."秒";
        //以下两条获取服务器时间，中国大陆采用的是东八区的时间,设置时区写成Etc/GMT-8
        date_default_timezone_set("Etc/GMT-8");
        $systemtime = date("Y-m-d H:i:s",time());

        $this->assign('sysos',$sysos);
        $this->assign('sysversion',$sysversion);
        $this->assign('mysqlinfo',$mysqlinfo);
        $this->assign('gdinfo',$gdinfo);
        $this->assign('freetype',$freetype);
        $this->assign('allowurl',$allowurl);
        $this->assign('max_upload',$max_upload);
        $this->assign('max_ex_time',$max_ex_time);
        $this->assign('systemtime',$systemtime);
        //\app\api\controller\Server::getStatus();
        return $this->fetch('server');
    }
    public function reviseuser(){
        return $this->fetch('revise-user');
    }
    public function revisecon(){
        return $this->fetch('revise-con');
    }
    public function revisecom(){
        return $this->fetch('revise-com');
    }
    public function detailsuser($username){
        $user=\app\api\model\Users::get(['UserName'=>$username]);
        if($user){
            $this->assign('user',$user);
        }else{
            $this->error('未知用户信息');
        }
        return $this->fetch('details-user');
    }
    public function detailscon($username){
        $user=\app\api\model\Contractors::get(['UserName'=>$username]);
        if($user){
            $this->assign('user',$user);
        }else{
            $this->error('未知用户信息');
        }
        return $this->fetch('details-con');
    }
    public function detailscom(){
        $company=\app\api\model\Companies::get(['Name'=>$name]);
        if($company){
            $this->assign('company',$company);
        }else{
            $this->error('未知公司信息');
        }
        return $this->fetch('details-com');
    }
    public function contractors(){
        $list=\app\api\model\Contractors::all();
        $this->assign('list',$list);
        return $this->fetch('contractors');
    }
    public function companies(){
        $list=\app\api\model\Companies::all();
        $this->assign('list',$list);
        return $this->fetch('companies');
    }
    public function addcon(){
        
        return $this->fetch('add-con');
    }
    public function addcom(){
        return $this->fetch('add-com');
    }
    public function user(){
        $list=\app\api\model\Users::all();
        $this->assign('list',$list);
        return $this->fetch('user');
    }
    public function getstatus(){
        $server=new \app\api\controller\Server();
        return $server->getstatus();
    }
    public function getlog(){
        \think\Log::record('读取数据');
        return json(\think\Log::getLog());
    }
}