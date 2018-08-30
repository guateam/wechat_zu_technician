<?php
namespace app\api\controller;
use think\Controller;
use app\api\model\Contractors as UserModel;
/**
 * 发包人员数据库操作类
 * 部分实装
 */
class Contractors extends Controller{
    /**
     * 注册方法
     */
    public function register(){
        $data=new UserModel($_POST);
        try{
            $data->allowField(true)->save();
            return json(['status'=>1]);
        }catch(Exception $e){
            return json(['status'=>0]);
        }
    }
    /**
     * 登陆方法
     */
    public function login(){
        $data=UserModel::get($_POST['UserName']);//从数据库调取此用户信息
        if($data){//判断是否有此用户名，感觉没必要告诉用户数据库是否为空，而且不知道model的空数据怎么检测所以不管了~
            if($data->Password!=$_POST['Password']){
                return json(['status'=>0]);
            }else{
                /**
                 * 这里如果是图片的话应该要转存一下吧
                 */
                if($_FILES['FaceInform']['error']>0){
                    return json(['status'=>-1]);
                }else{
                    if(move_uploaded_file($_FILES["file"]["tmp_name"],"upload/" . $_FILES["file"]["name"])){
                        $uploadFile="upload/" . $_FILES["file"]["name"];
                    }else{
                        return json(['status'=>-1]);
                    }
                }
                if(exec('FaceCompare.exe '.$data->FaceInformation.' '.$uploadFile)==0){
                    return json(['status'=>-1]);//调用红软的sdk并比对数据，感觉传回来的应该是个图片，这里调用exe文件 第一个是数据库中的文件 第二个是传输过来的文件
                }else{
                    return json(['status'=>1]);
                }
            }
        }else{
            return json(['status'=>-2]);
        }
    }
    /**
     * 修改用户信息
     * 修改 2018-3-24 张煜
     */
    public function change($id,$name,$gender,$birthday,$nation,$phonenumber,$emergencyname,$emergencyphone,$password){
        $userid=$this->checkuser($id);
        if($userid){
            $user=UserModel::get(["ContractorID"=>$userid]);
            if($user){
                $user->data([
                    "UserName"=>$name,
                    "Gender"=>$gender,
                    "Birthday"=>$birthday,
                    "Nation"=>$nation,
                    "PhoneNumber"=>$phonenumber,
                    "EmergencyContactName"=>$emergencyname,
                    "EmergencyContactPhone"=>$emergencyphone
                ]);
                $user->save();
                if($password!=""){
                    $user->data(['Password'=>$password]);
                    $user->save();
                }
            }
        }
    }
    /**
     * 获取用户信息
     */
    public function read(){
        $user=UserModel::get($_POST['UserName']);
        if($user){
            $data=['UserName'=>$user->UserName,
                    'Password'=>$user->Password,
                    'Gender'=>$user->Gender,
                    'Birthday'=>$user->Birthday,
                    'Idcard'=>$user->Idcard,
                    'Nation'=>$user->Nation,
                    'Height'=>$user->Height,
                    'MaritalStatus'=>$user->MaritalStatus,
                    'PhoneNumber'=>$user->PhoneNumber,
                    'EmergencyContactName'=>$user->EmergencyContactName,
                    'EmergencyContactPhone'=>$user->EmergencyContactPhone,
                    'BankCardNumber'=>$user->BankCardNumber,
                    'FaceInformation'=>$user->FaceInformation,
                    'Logintime'=>$user->Logintime,
                    'Jointime'=>$user->Jointime,
                    'Company'=>$user->Company,
                    'WorkTime'=>$user->WorkTime,
                    'SecurityPermissions'=>$user->SecurityPermissions,
                ];
            return json($data);
        }else{
            return json(['ststus'=>0]);
        }

    }

    public function getusername($id){//通过id获取发包人员姓名
        $user=UserModel::get(["ContractorID"=>$id]);
        if($user){
            return $user->UserName;
        }else{
            return '未知';
        }
    }
    /**
     * 检查cookie中的id序列号是否符合并返回真正id
     * 2018-3-4 张煜
     */
    public  function checkuser($id){
        $user=UserModel::get(["Cookie"=>$id]);
        if($user){
            return $user->ContractorID;
        }else{
            return 0;
        }
    }
    /**
     * 人脸检测
     * 2018-3-12袁宜照
     * 2018-3-29袁宜照更新
     * $user->FaceInformation改为文件夹名
     */
    public function facecheck($phonenumber,$faceinform)
    {
        $user=UserModel::get(["PhoneNumber"=>$phonenumber]);
        //打开用户人脸信息文件夹
        if($user)
        {
            $toomany = false;
            $handler = opendir("C:\\\\AppServ\\www\\tp5\\public\\static\\faceinform\\".$user->FaceInformation);
            $faceinform_list = array();
            while( ($filename = readdir($handler)) !== false ) 
            {
            //略过linux目录的名字为'.'和‘..'的文件
                if($filename != '.' && $filename != '..')
                {  
                //保存人脸图片名
                    array_push($faceinform_list,$filename);
                }
            }

        
            for($i = 0;$i<count($faceinform_list);$i++)
            {
                $command='C:\\\\AppServ\\www\\tp5\\public\\static\\camera\\facecheck.exe C:\\\\AppServ\\www\\tp5\\public\\static\\camera\\pictures\\'
                            .$faceinform.' C:\\\\AppServ\\www\\tp5\\public\\static\\faceinform\\'
                            .$user->FaceInformation."\\".$faceinform_list[$i];
                $degree=exec($command);
                if($degree>0.60)
                {
                    unlink("C:\\\\AppServ\\www\\tp5\\public\\static\\camera\\pictures\\".$faceinform);
                    return json(['status'=>'1','degree'=>$degree,'source'=>$faceinform_list[$i]]);
                }else if($degree =="too many face0"){
                    $toomany = true;
                }else {
                    $toomany = false;
                    continue;
                }
            }
            unlink("C:\\\\AppServ\\www\\tp5\\public\\static\\camera\\pictures\\".$faceinform);
            if($toomany)return json(['status'=>'toomany','degree'=>$degree,'source'=>$faceinform_list[$i-1]]);
            else return json(['status'=>'0','degree'=>$degree]);
        }
        else return null;
    }
    /**
     * 通过cookie实现面部识别
     * 2018-3-25 张煜
     * 2018-3-29袁宜照更新
     * $user->FaceInformation改为文件夹名
    */
    public function facecheckbycookie($cookie,$faceinform){
        $user=UserModel::get(["Cookie"=>$cookie]);
        //打开用户人脸信息文件夹
        $handler = opendir("C:\\\\AppServ\\www\\tp5\\public\\static\\faceinform\\".$user->FaceInformation);
        $faceinform_list = array();
        while( ($filename = readdir($handler)) !== false ) 
        {
        //略过linux目录的名字为'.'和‘..'的文件
            if($filename != '.' && $filename != '..')
            {  
                //保存人脸图片名
                array_push($faceinform_list,$filename);
            }
        }
        if($user)
        {   
            $toomany = false;
            for($i = 0;$i<count($faceinform_list);$i++)
            {
                
                $command='C:\\\\AppServ\\www\\tp5\\public\\static\\camera\\facecheck.exe C:\\\\AppServ\\www\\tp5\\public\\static\\camera\\pictures\\'
                            .$faceinform.' C:\\\\AppServ\\www\\tp5\\public\\static\\faceinform\\'
                            .$user->FaceInformation."\\".$faceinform_list[$i];
                $degree=exec($command);
                if($degree>0.60)
                {
                    unlink("C:\\\\AppServ\\www\\tp5\\public\\static\\camera\\pictures\\".$faceinform);
                    return json(['status'=>'1','degree'=>$degree,'source'=>$faceinform_list[$i]]);
                }else if($degree =="too many face0"){
                   $toomany = true;
                }else {
                    $toomany = false;
                    continue;
                }

            }
            unlink("C:\\\\AppServ\\www\\tp5\\public\\static\\camera\\pictures\\".$faceinform);
            if($toomany)return json(['status'=>'toomany','degree'=>$degree,'source'=>$faceinform_list[$i-1]]);
            else return json(['status'=>'0','degree'=>$degree]);
        }
        else return null;
    }
    /**
     * 实现通过手机号码登陆
     * 实装
     * 2018-3-4 张煜
     * 2018-3-12 袁宜照，将人脸检测和登录分离，检测人脸的过程在html页面已经完成
     * 2018-3-16 yyz 修改登陆逻辑
     */
    public function loginbyphonenumber($phonenumber,$password){
        $user=UserModel::get(["PhoneNumber"=>$phonenumber]);
        if($user){
                if($user->Password===$password){
                    $cookie=$this->getcookie();
                    $user->Cookie=$cookie;
                    $user->OnlineStatus=1;
                    $user->Logintime=date('Y-m-d H:s');
                    $user->save();
                    return json([
                        "status"=>1,
                        "id"=>$cookie,
                        "type"=>'contractor',
                        "link"=>"/tp5/public/index.php/contractor/index/index"
                    ]); 
                }else{
                    return json([
                    "status"=>0
                    ]);
                }
        }else{
            return null;
        }
    }
    /**
     * 内部方法，生成一个不重复的cookie
     */
    private function getcookie(){
        $cookie=$this->makekeys();
        if(UserModel::get(["Cookie"=>$cookie])){
            $cookie=$this->getcookie();
        }
        return $cookie;
    }
    /**
     * 内部方法，生成随机的一串代码
     */
    private function makekeys( $length = 8 ) { 
 
		// 密码字符集，可任意添加你需要的字符 
		$chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 
		'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's', 
		't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D', 
		'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O', 
		'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z', 
		'0', '1', '2', '3', '4', '5', '6', '7', '8', '9'); 
 
		// 在 $chars 中随机取 $length 个数组元素键名 
			
		$keys = ''; 
		for($i = 0; $i < $length; $i++) { 
		    // 将 $length 个数组元素连接成字符串 
				$keys .= $chars[mt_rand(0,61)]; 
		} 
		return $keys; 
    }
    /**
     * 获取接包人员的等待列表
     * 包括
     * bug 操作 资源
     * 2018-3-6 张煜
     * 2018-3-16 张煜
     */
    public function waitinglist($id){
        $userid=$this->checkuser($id);
        if($userid){
            $email=new \app\api\controller\Email();
            $buglist=$email->getwaitingbug($userid);
            $project=new \app\api\controller\Project();
            $controllist=$project->getcontrollist($userid);
            $log=new \app\api\controller\Log();
            $resourcechange=$log->getresourcechange($userid);
            $resultupload=$project->getresultupload($userid);
            $resourcelist=$email->getwaitingresource($userid);
            return array_merge($buglist,$resourcechange,$controllist,$resultupload,$resourcelist);
        }
    }
    /**
     * 获取该发包人员的聊天
     * 2018-3-7 张煜
     */
    public function chatingbox($id){
        $chartingbox=new \app\api\controller\ChatingBox();
        $project=new \app\api\controller\Project();
        $projectidlist=$project->getprojectlist($id);
        $data=$chartingbox->getallbyprojectid($projectidlist);
        return $data;
    }
    /**
     * 获取用户信息
     * 2018-3-24 张煜
     */
    public function getuserdetail($userid){
        $user=UserModel::get(['ContractorID'=>$userid]);
        if($user){
            $data=[
                'name'=>$user->UserName,
                'gender'=>$user->Gender,
                'birthday'=>$user->Birthday,
                'nation'=>$user->Nation,
                'phonenumber'=>$user->PhoneNumber,
                'emergencyname'=>$user->EmergencyContactName,
                'emergencyphone'=>$user->EmergencyContactPhone,
                'password'=>''
            ];
            return $data;
        }
    }
    /**
     * 登出方法
     * 2018-3-24 张煜
     */
    public function logout($userid){
        $user=UserModel::get(['Cookie'=>$userid]);
        if($user){
            $user->Cookie="";
            $user->OnlineStatus=0;
            $user->save();
        }
    }
    /**
     * 自动填充的查找功能
     * 2018-3-24 张煜
     */
    public function autoc($term){
        $list=\think\Db::query("select * from contractor where UserName like '%".$term."%'");
        $data=[];
        foreach ($list as $value) {
            $item=["label"=>$value['UserName']."(发包员)","value"=>"c".$value['ContractorID']];
            array_push($data,$item);
        }
        return $data;
    }
    /**
     * 找回密码
     * 2018-4-3袁宜照
     */
    public function newpassword($phone,$newpassword)
    {
        $user=UserModel::get(['PhoneNumber'=>$phone]);
        if($user)
        {
            $user->Password = $newpassword;
            $user->save();
            return $user->Password;
        }
        else return null;
    }
    /**
     * 获取头像
     * 2018-4-7 张煜
     */
    public function gethead($userid){
        $user=UserModel::get(["ContractorID"=>$userid]);
        if($user){
            $handler = opendir("C:\\\\AppServ\\www\\tp5\\public\\static\\faceinform\\".$user->FaceInformation);
            $faceinform_list = array();
            while( ($filename = readdir($handler)) !== false ) 
            {
            //略过linux目录的名字为'.'和‘..'的文件
                if($filename != '.' && $filename != '..')
                {  
                //保存人脸图片名
                    array_push($faceinform_list,$filename);
                }
            }
            return "/tp5/public/static/faceinform/".$user->FaceInformation."/".$faceinform_list[0];
        }
        return "/tp5/public/static/image/3.jpg";
    }
}