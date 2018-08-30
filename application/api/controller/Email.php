<?php
namespace app\api\controller;
use think\Controller;
use app\api\model\Email as EmailModel;
/**
 * email数据库操作类
 * 部分实装
 */
class Email extends Controller{
    /**
     * 通过id获取当前id的接包人员前十封未读信件
     * 已实装
     * 需要修改 
     * 2018-3-15 张煜修改
     */
    public function getemaillistforcontractortop($id){//获取收件箱前十contractor
        $emaillist=\think\Db::query("select * from email where RecipientsID like '%,c".$id.",%'");
        $data1=[];
        $key=0;
        $isred=false;
        foreach ($emaillist as $value) {
            if(strpos($value['State'],(',c'.$id.','))!==false){
                if($value['Type']=='全网通知'){
                    $isred=true;
                }
                $item=[
                    "id"=>$value['ID'],
                    "name"=>$value['Name'],
                    "poster"=>$this->getpostername($value['PosterID']),
                    "time"=>$this->gettimebefore($value['Time']),
                    "link"=>'/tp5/public/index.php/contractor/email/index/id/'.$value['ID'],
                    "img"=>'/tp5/public/static/image/2.jpg'
                ]; 
                array_push($data1,$item); 
                $key++;
            }
        }
        $data=[];
        if($key>9){
            for ($i=$key; $i >($key-10); $i--) { 
            array_push($data,$data1[$i]);
            }
        }else{
            $data=$data1;
        }
        $back=[
            "data"=>$data,
            "counter"=>$key,
            "status"=>1,
            "isred"=>$isred
        ];
        return json($back);
    }
    /**
     * 通过id获取发包人员所有信件
     * 已实装
     */
    public function getemaillistforcontractor($id){//获取收件箱contractor
        $emaillist=\think\Db::query("select * from email where RecipientsID like '%,c".$id.",%'");
        $data=[];
        foreach($emaillist as $email){
            $item=[
                "id"=>$email["ID"],
                "name"=>$email["Name"],
                "time"=>$email["Time"],
                "note"=>$email["Note"],
                "poster"=>$this->getpostername($email['PosterID']),
                "link"=>'jumptodetail('.$email["ID"].')'
            ];
            array_push($data,$item);
        }
        return $data;
    }
    /**
     * 通过id获取接包人员的前十封未读信件
     * 已实装
     * 需要修改
     * 2018-3-15 张煜修改
     */
    public function getemaillistforusertop($id){//获取收件箱前十user
        $emaillist=\think\Db::query("select * from email where RecipientsID like '%,u".$id.",%'");
        $data1=[];
        $key=0;
        $isred=false;
        foreach ($emaillist as $value) {
            if(strpos($value['State'],(',u'.$id.','))!==false){
                if($value['Type']=='全网通知' or $value['Type']=="回复"){
                    $isred=true;
                }
                $item=[
                    "id"=>$value['ID'],
                    "name"=>$value['Name'],
                    "poster"=>$this->getpostername($value['PosterID']),
                    "time"=>$this->gettimebefore($value['Time']),
                    "link"=>'/tp5/public/index.php/user/email/index/id/'.$value['ID'],
                    "img"=>'/tp5/public/static/image/2.jpg'
                ]; 
                array_push($data1,$item);
                $key++;
            }
        }
        $data=[];
        if($key>9){
            for ($i=$key; $i >($key-10); $i--) { 
            array_push($data,$data1[$i]);
            }
        }else{
            $data=$data1;
        }
        $back=[
            "data"=>$data,
            "counter"=>$key,
            "status"=>1,
            "isred"=>$isred
        ];
        return json($back);
    }
    /**
     * 通过id获取接包人员的所有信件
     * 已实装
     */
    public function getemaillistforuser($id){//获取收件箱user
        $emaillist=\think\Db::query("select * from email where RecipientsID like '%,u".$id.",%'");
        $data=[];
        foreach($emaillist as $email){
            $item=[
                "id"=>$email["ID"],
                "name"=>$email["Name"],
                "time"=>$email["Time"],
                "note"=>$email["Note"],
                "poster"=>$this->getpostername($email['PosterID']),
                "link"=>'jumptodetail('.$email["ID"].')'
            ];
            array_push($data,$item);
        }
        return $data;
    }
    /**
     * 通过id获取发件人姓名 id=（c/u）+id
     * 已实装
     * 修改 2018-3-24 张煜
     */
    private function getpostername($id){//获取发件人姓名
        if(strpos($id,'c')!==false){
            $user=new \app\api\controller\Contractors();
            $userid=(explode('c',$id))[1];
            return '发包员 '.$user->getusername($userid);
        }else if(strpos($id,'u')!==false){
            $user=new \app\api\controller\Users();
            $userid=(explode('u',$id))[1];
            return '接包员 '.$user->getusername($userid);
        }else if(strpos($id,'a')!==false){
            $user=new \app\api\controller\Administractors();
            $userid=(explode('a',$id))[1];
            return '管理员 '.$user->getusername($userid);
        }else{
            return '未知';
        }
    }
    /**
     * 内部方法 获取收件人姓名
     * 已实装
     * 2018-3-3 张煜
     */
    private function getrecipientsname($id){//获取收人的姓名，XXXX XXXX的方式
        $list=explode(",",$id);
        $data='';
        foreach ($list as $value) {
            if($value){
                $data=$data.$this->getpostername($value).' ';
            }
        }
        return $data;
    }
    /**
     * 内部方法 获取发件时间距离现在过去几分钟
     * 已实装
     * 2018-3-3 张煜
     */
    private function gettimebefore($time){//获取距离现在时间
        $nowtime=date('Y-m-d H:i');
        $now = strtotime($nowtime);
        $old = strtotime($time);
        $durlingtime=round((($now-$old))/60);
        if($durlingtime>=60 && $durlingtime<1440){
            return round($durlingtime/60).'小时';
        }else if($durlingtime>=1440){
            return round($durlingtime/1440).'天';
        }else{
            return $durlingtime.'分钟';
        }  
    }
    /**
     * 通过id获取信件所有内容
     * 部分实装
     * 缺少附件部分
     * 需要修改
     * 2018-3-4 张煜
     */
    public function getemaildetail($id){//获取email的详细内容
        $email=EmailModel::get(["ID"=>$id]);
        $data=[];
        if($email){
            $data=[
                "name"=>$email->Name,
                "time"=>$email->Time,
                "poster"=>$this->getpostername($email->PosterID),
                "recipients"=>$this->getrecipientsname($email->RecipientsID),
                "note"=>$email->Note,
                "replyid"=>$email->PosterID
            ];
            return $data;
        }
    }
    /**
     * 设置已读
     * 未实装i
     * 2018-3-5 张煜
     */
    public function setRead($id,$userid){
        $email=EmailModel::get(['ID'=>$id]);
        $email->State=str_replace($userid,'',$email->State);
        $email->save();
    }
    /**
     * 发送邮件
     * 2018-3-5 张煜
     */
    public function send($name,$poster,$recipients,$type,$note,$from,$resource){
        $email=new EmailModel();
        if($from==1){
            $user=new \app\api\controller\Users();
            $posterid='u'.$user->checkuser($poster);
        }else{
            $user=new \app\api\controller\Contractors();
            $posterid='c'.$user->checkuser($poster);
        }
        $email->data([
            "Name"=>$name,
            "PosterID"=>$posterid,
            "RecipientsID"=>$recipients,
            "Type"=>$type,
            "Note"=>$note,
            "Resources"=>json($resource),
            "Time"=>date('Y-m-d H:i'),
            "State"=>$recipients,
            ]);
        $email->save();
        if($type=="Bug"){
            $chartingbox=new \app\api\controller\ChatingBox();
            $chartingbox->send('接包员 '.$user->getusername($user->checkuser($poster)),'Debug',date('Y-m-d H:i'),$name,\explode('@',$note)[1]);
        }
        return json(["status"=>1]);
    }
    /**
     * 发送全网通知
     * 2018-3-24 张煜
     */
    public function sendall($name,$poster,$note){
        $email=new EmailModel();
        $user=new \app\api\controller\Administractors();
        $posterid='a'.$user->checkuser($poster);
        $recipients=$this->getallusers();
        $email->data([
            "Name"=>$name,
            "PosterID"=>$posterid,
            "RecipientsID"=>$recipients,
            "Type"=>'全网通知',
            "Note"=>$note,
            "Resources"=>'{}',
            "Time"=>date('Y-m-d H:i'),
            "State"=>$recipients,
            ]);
        $email->save();
        $chartingbox=new \app\api\controller\ChatingBox();
        $chartingbox->send('管理员 '.$user->getusername($user->checkuser($poster)),'管理员',date('Y-m-d H:i'),$name,0);
    }
    /**
     * 获取所有发包接包人员
     * 2018-3-24 张煜
     */
    public function getallusers(){
        $list1=\app\api\model\Administractors::all();
        $list2=\app\api\model\Contractors::all();
        $list3=\app\api\model\Users::all();
        $data=',';
        foreach($list1 as $value){
            $data=$data."a".$value->AdministratorsID.",";
        }
        foreach($list2 as $value){
            $data=$data."c".$value->ContractorID.",";
        }
        foreach($list3 as $value){
            $data=$data."u".$value->UserID.",";
        }
        return $data;
    }
    /**
     * 获取bug的提交申请
     */
    public function getwaitingbug($id){
        $emaillist=\think\Db::query("select * from email where RecipientsID like '%,c".$id.",%'");
        $data=[];
        foreach($emaillist as $value){
            if((strpos($value['State'],(',c'.$id.','))!==false) and ($value['Type']=='bug')){
                if(strpos($value['Note'],"@")!==false){
                    $projectid=explode('@',$value['Note'])[1];
                    $project=new \app\api\controller\Project();
                    $projectname=$project->getprojectname($projectid);
                    $item=[
                        'name'=>$value['Name'],
                        'poster'=>$this->getpostername($value['PosterID']),
                        'time'=>$value['Time'],
                        'project'=>$projectname,
                        'note'=>explode('#',$value['Note'])[1],
                        'level'=>'高',
                        'type'=>'bug',
                        'link'=>'/tp5/public/index.php/contractor/email/index/id/'.$value['ID'],
                        'color'=>'danger'
                    ];
                    array_push($data,$item);
                }
            }
            
        }
        return $data;
    }
    /**
     * 获取资源的获取申请
     */
    public function getwaitingresource($id){
        $emaillist=\think\Db::query("select * from email where RecipientsID like '%,c".$id.",%'");
        $data=[];
        foreach($emaillist as $value){
            if((strpos($value['State'],(',c'.$id.','))!==false) and ($value['Type']=='资源')){
                if(strpos($value['Note'],"@")!==false){
                    $projectid=explode('@',$value['Note'])[1];
                    $project=new \app\api\controller\Project();
                    $projectname=$project->getprojectname($projectid);
                    $item=[
                        'name'=>$value['Name'],
                        'poster'=>$this->getpostername($value['PosterID']),
                        'time'=>$value['Time'],
                        'project'=>$projectname,
                        'note'=>explode('#',$value['Note'])[1],
                        'level'=>'低',
                        'type'=>'资源',
                        'link'=>'/tp5/public/index.php/contractor/email/index/id/'.$value['ID'],
                        'color'=>'success'
                    ];
                    array_push($data,$item);
                }
            }
            
        }
        return $data;
    }
    /**
     * 获取当前id下的buglist
     * 2018-3-16 张煜
     * 修改 2018-3-24 张煜
     */
    public function getbuglistbyid($id,$userid){
        $data1=EmailModel::all(["Type"=>'bug']);
        $data2=EmailModel::all(["Type"=>'资源']);
        $data=[];
        foreach(array_merge($data1,$data2) as $value){
            if(strpos($value->Note,"@")!==false){
                if(explode('@',$value->Note)[1]==$id){
                    if(strpos($value->State,(',c'.$userid.','))!==false){
                        $item=[
                            'name'=>$value->Name,
                            'status'=>'等待操作',
                            'link'=>'/tp5/public/index.php/contractor/email/index/id/'.$value->ID
                        ];
                        if($value->Type=='bug'){
                            $item=array_merge($item,['type'=>'bug','color'=>'danger']);
                        }else{
                            $item=array_merge($item,['type'=>'资源','color'=>'warning']);
                        }
                        array_push($data,$item);
                    }
                }
            }
        }
        return $data;
    }
    /**
     * 获取人员信息
     * 2018-3-24 张煜
     */
    public function getuserlist($term){
        $list1=\think\Db::query("select * from administrators where UserName like '%".$term."%'");
        $list2=\think\Db::query("select * from contractor where UserName like '%".$term."%'");
        $list3=\think\Db::query("select * from user where UserName like '%".$term."%'");
        $data=[];
        foreach($list1 as $value){
            $item=["label"=>$value['UserName']."(管理员)","value"=>"a".$value['AdministratorsID']];
            array_push($data,$item);
        }
        foreach($list2 as $value){
            $item=["label"=>$value['UserName']."(发包员)","value"=>"c".$value['ContractorID']];
            array_push($data,$item);
        }
        foreach($list3 as $value){
            $item=["label"=>$value['UserName']."(接包员)","value"=>"u".$value['UserID']];
            array_push($data,$item);
        }
        return $data;
    }
}