<?php
namespace app\api\controller;
use think\Controller;
use app\api\model\Users as UserModel;
/**
 * 用户数据库操作类
 * 部分实装
 */
class Users extends Controller{
    /**
     * 注册方法
     */
    public function register(){
        $data=new UserModel($_POST);
        try{
            $data->allowField(true)->save();
            $company=new \app\api\controller\Companies();
            $company->addmembers($data->Company,[$data->UserID]);
            return json(['status'=>1]);
        }catch(Exception $e){
            return json(['status'=>0]);
        }
    }
    /**
     * 登陆方法
     * @test
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
     * 2018-3-24 修改
     * @test
     */
    public function change($id,$name,$gender,$birthday,$idcard,$phonenumber,$company,$advantage,$emergencyname,$emergencyphone,$password){
        $userid=$this->checkuser($id);
        if($userid){
            $user=UserModel::get(["UserID"=>$userid]);
            if($user){
                $companyid=new \app\api\controller\Companies();
                $advantagelist=explode(" ",$advantage);
                array_filter($advantagelist);
                $user->data([
                    "UserName"=>$name,
                    "Gender"=>$gender,
                    "Birthday"=>$birthday,
                    "Idcard"=>$idcard,
                    "PhoneNumber"=>$phonenumber,
                    "Company"=>$companyid->getcompanyidforuser($company),
                    "Advantage"=>json_encode($advantagelist),
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
                    'CreditRating'=>$user->CreditRating
                ];
            return json($data);
        }else{
            return json(['ststus'=>0]);
        }

    }
    /**
     * 通过id获取用户雷达图数据
     * 已实装
     */
    public function getradardata($id){//获取雷达图所需的数据
        $user=UserModel::get(['UserID'=>$id]);
        $project= new \app\api\controller\Project();
        $list=$project->getprojectbyuserid($id);
        $count=count($list);
        if($count==0){
            $count=1;
        }
        $language=json_decode($user->Advantage,true);
        $languagecount=count($language);
        $delay=0;
        foreach($list as $value){
            
            if($value['State']=='延期'){
                $delay++;
            }elseif($value['State']=='完成'){
                $end1=strtotime($value['Endtime']);
                $end2=strtotime($value['ProjectEndtime']);
                if($end1<$end2){
                    $delay++;
                }
            }
        }

        $ontime=(($count-$delay)/$count)*100;
        $userdata=[
            $count,
            $languagecount,
            $user->WorkTime,
            $ontime,
            $user->SecurityPermissions,
            $user->Price,
            $user->CreditRating,
            $list
        ];
        return $userdata;
    }
    /**
     * 获取用户的雷达图
     * 已实装
     */
    public function getradarmap($userid){//获取雷达图
        $user=UserModel::get(['UserID'=>$userid]);
        $list=UserModel::all();
        $company=new \app\api\controller\Companies();
        $companydata=$company->getradardata($user->Company);
        $userdata=$this->getradardata($userid);
        $maxprice=0;
        $maxtime=0;
        foreach($list as $value){
            if($maxprice<$value->Price){
                $maxprice=$value->Price;
            }
            if($maxtime<$value->WorkTime){
                $maxtime=$value->WorkTime;
            }
        }
        $data=[
            "radar"=>[
                "indicator"=>[
                    [
                        'name' => '参与项目',
                        'max' => count(\app\api\model\Project::all())
                    ],
                    [
                        'name' => '语种数量',
                        'max' => 10
                    ],
                    [
                        'name' => '工作时长',
                        'max' => $maxtime
                    ],
                    [
                        'name' => '准时率',
                        'max' => 100
                    ],
                    [
                        'name' =>'安全等级',
                        'max' => 2
                    ],
                    [
                        'name' =>'市场价值创造',
                        'max' => $maxprice
                    ],
                    [
                        'name' =>'信誉等级',
                        'max' => 5
                    ],
                ]
            ],
            "series"=>[
                [
                    "name" => '能力',
                    "data" => [
                        [
                            "value"=>$userdata,
                            "name"=>"个人能力"
                        ],
                        [
                            "value"=>$companydata,
                            'name'=>'公司水平'
                        ]
                    ]
                ]
            ]
        ];
        return $data;
    }
    /**
     * 通过id获取接包人员的基本信息
     * 已实装
     */
    public function getbasicuserbyid($id){
        $user=UserModel::get(["UserID"=>$id]);
        $company= new \app\api\controller\Companies();
        $companyname=$company->getcompanyname($user->Company);
        $advantage='';
        foreach(json_decode($user->Advantage) as $value){
            $advantage=$advantage.$value." ";
        }
        $data=[
            "name"=>$user->UserName,
            "gender"=>$user->Gender,
            "age"=>$user->Age,
            "phonenumber"=>$user->PhoneNumber,
            "companyname"=>$companyname,
            "companyid"=>$user->Company,
            "safetygrade"=>$user->SecurityPermissions,
            "creditrating"=>$user->CreditRating,
            "advantage"=>$advantage,
            "worktime"=>$user->WorkTime,
            "jointime"=>$user->Jointime,
            "logintime"=>$user->Logintime,
            "img"=>$this->gethead($id),
            "grade"=>$user->Grade,
            'price'=>$user->Price
        ];
        return $data;
    }
    /**
     * 通过id获取当前接包人员工作情况
     * 2018-3-7 张煜
     */
    public function getuserworkingstate($id){
        $user=UserModel::get(["UserID"=>$id]);
        $project=new \app\api\controller\Project();
        $list1=$project->getuserwaitingprojectbyuserid($id);
        $list2=$project->getuserworkingprojectbyuserid($id);
        $count=count($list1)+count($list2);
        $busylevel='';
        $state='';
        if($count>=6){
            $busylevel='繁忙';
        }else if($count>=4){
            $busylevel='正常';
        }else{
            $busylevel='空闲';
        }
        switch ($user->OnlineStatus) {
            case 1:
                $state='在线';
                break;
            case 2:
                $state='工作中';
                break;
            case 0:
                $state='离线';
                break;
            default:
                # code...
                break;
        }
        $data=[
            'name'=>$user->UserName,
            'safetygrade'=>$user->SecurityPermissions,
            'state'=>$state,
            'busylevel'=>$busylevel,
            'id'=>$user->UserID
        ];
        return $data;
    }
    /**
     * 通过id获取接包人员id
     * 已实装
     */
    public function getusername($id){//通过id获取接包人员姓名
        $user = UserModel::get(["UserID"=>$id]);
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
    public function checkuser($id){
        $user=UserModel::get(["Cookie"=>$id]);
        if($user){
            return $user->UserID;
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
     * 2018-3-16 yyz 登陆逻辑修改
     */
    public function loginbyphonenumber($phonenumber,$password){
        $user=UserModel::get(["PhoneNumber"=>$phonenumber]);
        if($user){
            if($user->State == 1)
            {
                if($user->Password===$password){
                    $cookie=$this->getcookie();
                    $user->Cookie=$cookie;
                    $user->OnlineStatus=1;
                    $user->Logintime=date("Y-m-d H:s");
                    $user->save();
                    return json([
                        "status"=>1,
                        "id"=>$cookie,
                        "type"=>'user',
                        "link"=>"/tp5/public/index.php/user/index/index"
                    ]); 
                }else{
                    return json([
                    "status"=>0
                    ]);
                }
            }
            else
            {
                return json([
                    "status"=>-1
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
     * 获取成员
     * 2018-3-7 张煜
     */
    public function getuserlist($safetygrade,$projectid){
        $list=UserModel::where('SecurityPermissions','>=',$safetygrade)->select();
        $data=[];
        $project=\app\api\model\Project::get(['ID'=>$projectid]);
        $useridlist=\explode(',',$project->UserID);
        foreach($list as $value){
            if(in_array($value->UserID,$useridlist)===false){
                $advantage='';
            foreach(json_decode($value->Advantage) as $value1){
                $advantage=$advantage.$value1.' ';
            }
            $company= new \app\api\controller\Companies();
            $companyname=$company->getcompanyname($value->Company);
            $project1=new \app\api\controller\Project();
            $list1=$project1->getuserwaitingprojectbyuserid($value->UserID);
            $list2=$project1->getuserworkingprojectbyuserid($value->UserID);
            $count=count($list1)+count($list2);
            $busylevel='';
            $busyleveln=2;
            $state='';
            if($count>=6){
                $busylevel='繁忙';
                $busyleveln=0;
            }else if($count>=4){
                $busylevel='正常';
                $busyleveln=1;
            }else{
                $busylevel='空闲';
                $busyleveln=2;
            }
            $item=[
                'name'=>$value->UserName,
                'advantage'=>$advantage,
                'company'=>$companyname,
                'creditrating'=>$value->CreditRating,
                'safetygrade'=>$value->SecurityPermissions,
                'worktime'=>$value->WorkTime,
                'id'=>$value->UserID,
                'grade'=>$value->Grade,
                'busylevel'=>$busylevel,
                'busyleveln'=>$busyleveln
            ];
            array_push($data,$item);
            }
        }
        return $data;
    }
    /**
     * 获取所有user
     * 2018-3-8 张煜
     */
    public function getalluserlist(){
        $list=UserModel::all();
        $data=[];
        foreach($list as $value){
            
            $advantage='';
            foreach(json_decode($value->Advantage) as $value1){
                $advantage=$advantage.$value1.' ';
            }
            $company= new \app\api\controller\Companies();
            $companyname=$company->getcompanyname($value->Company);
            $item=[
                'name'=>$value->UserName,
                'advantage'=>$advantage,
                'company'=>$companyname,
                'creditrating'=>$value->CreditRating,
                'safetygrade'=>$value->SecurityPermissions,
                'worktime'=>$value->WorkTime,
                'id'=>$value->UserID
            ];
            array_push($data,$item);
        }
        return $data;
    }
    /**
     * 获取idlist中的成员值
     */
    public function getuserdata($idlist){
        $data=[];
        foreach($idlist as $value){
            $user=UserModel::get(['UserID'=>$value]);
            $advantage='';
            foreach(json_decode($user->Advantage) as $value1){
                $advantage=$advantage.$value1.' ';
            }
            $company= new \app\api\controller\Companies();
            $companyname=$company->getcompanyname($user->Company);
            $item=[
                'name'=>$user->UserName,
                'advantage'=>$advantage,
                'company'=>$companyname,
                'creditrating'=>$user->CreditRating,
                'safetygrade'=>$user->SecurityPermissions,
                'worktime'=>$user->WorkTime,
                'id'=>$user->UserID
            ];
            array_push($data,$item);
        }
        return $data;
    }
    /**
     * 设置市场创造价值
     * 2018-3-21 张煜
     */
    public function addprice($id,$price){
        $user=UserModel::get(["UserID"=>$id]);
        if($user){
            $user->Price=$user->Price+$price;
            $user->save();
        }
    }
    /**
     * 设置人员评分
     * 2018-3-21 张煜
     */
    public function setgrade($id,$grade,$creditrating){
        $user=UserModel::get(["UserID"=>$id]);
        if($user){
            $user->CreditRating=$user->CreditRating*0.7+(($creditrating/10)*0.3);
            if($user->CreditRating>5){
                $user->CreditRating=5;
            }
            $user->Grade=$user->Grade*0.7+($grade*0.5+$creditrating*0.5)*0.25+$user->WorkTime*0.05;
            $user->save();
            $company=new \app\api\controller\Companies();
            $company->setCreditRating($user->Company);
        }
    }
    /**
     * 通过公司查找用户列表
     * 2018-3-21 张煜
     */
    public function getuserlistbycompany($id){
        $list=UserModel::all(['Company'=>$id]);
        $data=[];
        foreach($list as $value){
            $advantage='';
            foreach(json_decode($value->Advantage) as $value1){
                $advantage=$advantage.$value1.' ';
            }
            $item=[
                'name'=>$value->UserName,
                'advantage'=>$advantage,
                'creditrating'=>$value->CreditRating,
                'safetygrade'=>$value->SecurityPermissions,
                'worktime'=>$value->WorkTime,
                'id'=>$value->UserID
            ];
            array_push($data,$item);
        }
        return $data;
    }
    /**
     * 获取用户信息
     * 2018-3-24 张煜 
     */
    public function getuserdetail($userid){
        $user=UserModel::get(['UserID'=>$userid]);
        $company=new \app\api\controller\Companies();
        if($user){
            $companyname=$company->getcompanyname($user->Company);
            $advantage='';
            foreach(json_decode($user->Advantage) as $value1){
                $advantage=$advantage.$value1.' ';
            }
            $data=[
                'name'=>$user->UserName,
                'gender'=>$user->Gender,
                'birthday'=>$user->Birthday,
                'phonenumber'=>$user->PhoneNumber,
                'company'=>$companyname,
                'advantage'=>$advantage,
                'emergencyname'=>$user->EmergencyContactName,
                'emergencyphone'=>$user->EmergencyContactPhone,
                'password'=>'',
                'idcard'=>$user->Idcard
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
            $log=new Log();
            $worktime=$log->getoutlog($user->UserID,$user->UserName);
            $user->WorkTime+=$worktime;
            $company=new \app\api\controller\Companies();
            $user->save();
            $company->settotalworktime($user->Company);
        }
    }
    public function addworktime($userid,$worktime){
        $user=UserModel::get(['UserID'=>$userid]);
        if($user){
            $user->WorkTime+=$worktime;
            $company=new \app\api\controller\Companies();
            $user->save();
            $company->settotalworktime($user->Company);
        }
    }
    public function setOnlineState($userid,$state){
        $user=UserModel::get(['UserID'=>$userid]);
        if($user){
            $user->OnlineStatus=$state;
            $user->save();
        }
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
     * 用于头像显示
     * 2018-4-7 张煜
     */
    public function gethead($userid){
        $user=UserModel::get(["UserID"=>$userid]);
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
    public function isfirst($id){
        $userid=$this->checkuser($id);
        $user=UserModel::get(['UserID'=>$userid]);
        if($user){
            if($user->Idcard==''||$user->Advantage=='{}'){
                return json(['status'=>1]);
            }
        }
        return json(['status'=>0]);
    }
    public function autoc($term){
        $list=\think\Db::query("select * from user where UserName like '%".$term."%'");
        $data=[];
        foreach ($list as $value) {
            $item=["label"=>$value['UserName']."(接包员)","value"=>"u".$value['UserID']];
            array_push($data,$item);
        }
        return $data;
    }
}