<?php
namespace app\api\controller;
use think\Controller;
use app\api\model\Log as LogModel;


/**
 * Log操作类
 * 2018-3-9袁宜照
 */
class Log extends Controller{

    /**
     * 根据id获取log
     * 2018-3-9袁宜照
     * 修改 2018-3-9 张煜
     */
    public function getLogById($id)
    {
        $log = LogModel::get(['ID'=>$id]);
        if($log)
        {
            $data=[
                'time'=>$log->Time,
                'username'=>$log->UserName,
                'type'=>$log->State,
                'project'=>$project->getprojectname($log->ProjectID),
                'note'=>$log->Note
            ];
            return $data;
        }
    }
    /**
     * 获取所有Log
     * 2018-3-9 袁宜照
     * 修改 2018-3-9 张煜
     */
    public function getAllLog()
    {
        $data = [];
        $log = LogModel::all();
        if($log)
        {
            $project=new \app\api\controller\Project();
            foreach($log as $value){
                if($value->State=='资源修改申请'){
                    $item=[
                        'time'=>$value->Time,
                        'username'=>$value->UserName,
                        'type'=>$value->State,
                        'project'=>$project->getprojectname($value->ProjectID),
                        'note'=>'资源修改'
                    ];
                    array_push($data,$item);
                }else if($value->State=='签到'){
                    $item=[
                        'time'=>$value->Time,
                        'username'=>$value->UserName,
                        'type'=>$value->State,
                        'project'=>$project->getprojectname($value->ProjectID),
                        'note'=>'已签到'
                    ];
                    array_push($data,$item);
                }else if($value->State=='签退'){
                    $item=[
                        'time'=>$value->Time,
                        'username'=>$value->UserName,
                        'type'=>$value->State,
                        'project'=>$project->getprojectname($value->ProjectID),
                        'note'=>'已签退'
                    ];
                    array_push($data,$item);
                }else if($value->State=='登出'){
                    $item=[
                        'time'=>$value->Time,
                        'username'=>$value->UserName,
                        'type'=>$value->State,
                        'project'=>$project->getprojectname($value->ProjectID),
                        'note'=>'已登出'
                    ];
                    array_push($data,$item);
                }else{
                    $item=[
                        'time'=>$value->Time,
                        'username'=>$value->UserName,
                        'type'=>$value->State,
                        'project'=>$project->getprojectname($value->ProjectID),
                        'note'=>$value->Note
                    ];
                    array_push($data,$item);
                }
            }
            return $data;
        }
    }
    /**
     * 添加Log
     * 2018-3-9袁宜照
     */
    public static function addLog($proid,$username,$id)
    {
        $log = new LogModel();
        $result = $log->save(['UserName'=>$username,'State'=>'签到','Time'=>date("Y-m-d H:i"),'Note'=>'@'.$id.'@已签到','ProjectID'=>$proid]);
        if($result)return json(['state'=>'1']);
        else return json(['status'=>'0']);
    }
    public function getoutlog($id,$name){
        $log = new LogModel();
        $enddate=date("Y-m-d H:i");
        $log->data([
            'UserName'=>$name,
            'State'=>'登出',
            'Time'=>$enddate,
            'Note'=>'@'.$id.'@已登出',
            'ProjectID'=>0
        ]);
        $log->save();
        return $this->getworktime($id);
    }
    public function stoplog($id,$userid,$username){
        $log = new LogModel();
        $enddate=date("Y-m-d H:i");
        $log->data([
            'UserName'=>$username,
            'State'=>'签退',
            'Time'=>$enddate,
            'Note'=>'@'.$userid.'@已签退',
            'ProjectID'=>$id
        ]);
        $log->save();
        return $this->getworktime($id);
    }
    public function getworktime($id){
        $list=\think\Db::query("select * from log where Note like '%@".$id."@%'");
        $start=null;
        $end=[];
        $end1=[];
        foreach($list as $value){
            if($value['State']=="签到"){
                $start=$value;
            }else if($value['State']=="签退"){
                array_push($end,$value);
            }else if($value['State']=="登出"){
                array_push($end1,$value);
            }
        }
        if($end!=[] && $end1!=[]){
            if(count($end1)>=2){
                if($end[count($end)-1]['ID']<$start['ID']){
                        if(count($end1)>=2){
                            if($end1[count($end1)-1]['ID']<$start["ID"]){
                                return 0;
                            }else if($end1[count($end1)-1]['ID']>$start["ID"] && $end1[count($end1)-2]['ID']<$start["ID"]){
                                $timeset=\strtotime($end1[count($end)-1]["Time"])-\strtotime($start["Time"]);
                                $time=($timeset)%86400/3600;
                                return $time;
                            }else{
                                return 0;
                            }
                        }else{
                            if($end1[count($end1)-1]['ID']>$start["ID"]){
                                $timeset=\strtotime($end1[count($end)-1]["Time"])-\strtotime($start["Time"]);
                                $time=($timeset)%86400/3600;
                                return $time;
                            }
                            return 0;
                        }
                }else if($end[count($end)-1]['ID']>$start['ID'] && $end[count($end)-2]['ID']<$start['ID']){
                    $timeset=\strtotime($end[count($end)-1]["Time"])-\strtotime($start["Time"]);
                    $time=($timeset)%86400/3600;
                    return $time;
                }
            }else{
                if($end[count($end)-1]['ID']>$start['ID']){
                    $timeset=\strtotime($end[count($end)-1]["Time"])-\strtotime($start["Time"]);
                    $time=($timeset)%86400/3600;
                    return $time;
                }
            }
        }else if($end1!=[]){
            if(count($end1)>=2){
                if($end1[count($end1)-1]['ID']<$start["ID"]){
                    return 0;
                }else if($end1[count($end1)-1]['ID']>$start["ID"] && $end1[count($end1)-2]['ID']<$start["ID"]){
                    $time=(\strtotime($end1[count($end)-1]["Time"])-\strtotime($start["Time"]))%86400/3600;
                    return $time;
                }else{
                    return 0;
                }
            }else{
                if($end1[count($end1)-1]['ID']>$start["ID"]){
                    $time=(\strtotime($end1[count($end)-1]["Time"])-\strtotime($start["Time"]))%86400/3600;
                    return $time;
                }
            }
        }else{
            return 0;
        }
        return 0;
    }
    /**
     * 添加新资源修改请求log
     * 2018-3-16 张煜
     */
    public function addresourcechangelog($logger,$oldid,$changeid,$note,$user){
        $log = new LogModel();
        $log->data([
            'UserName'=>\app\api\model\Contractors::get(['ContractorID'=>\explode('c',$logger)[1]])->UserName,
            'State'=>'资源修改申请',
            'Time'=>date("Y-m-d H:i"),
            'ProjectID'=>'0',
            'Note'=>"#".$oldid."#"."@".$changeid."@"."$".$note."$"."(".\explode('c',$user)[1].")"
        ]);
        $log->save();
    }
    /**
     * 获取资源加载请求log
     * 2018-3-16 张煜
     */
    public function getresourcechange($userid){
        $log=\think\Db::query("select * from log where Note like '%(".$userid.")%'");
        $data=[];
        foreach($log as $value){
            if($value['State']=='资源修改申请'){
                $item=[
                    'name'=>'资源修改',
                    'poster'=>$value['UserName'],
                    'time'=>$value['Time'],
                    'project'=>'资源管理',
                    'note'=>explode('$',$value['Note'])[1],
                    'level'=>'高',
                    'type'=>'资源修改申请',
                    'link'=>'/tp5/public/index.php/contractor/resourcecheck/resourceaccept/oldid/'.(explode('#',$value['Note'])[1]).'/newid/'.(explode('@',$value['Note'])[1]).'/logid/'.$value['ID'],
                    'color'=>'warning'
                ];
                \array_push($data,$item);
            }
        }
        return $data;
    }
    /**
     * 成果文件上传log
     * 2018-3-16 张煜
     */
    public function addresultupload($id){
        $log=new LogModel();
        $log->data([

        ]);
    }
    /**
     * getprojectlog
     * 2018-3-16 张煜
     */
    public function getprojectlog($id){
        $log = LogModel::all(['ProjectID'=>$id]);
        $data=[];
        foreach($log as $value){
            $item=[
                'time'=>$value->Time,
                'username'=>$value->UserName,
                'type'=>$value->State,
                'note'=>$value->Note
            ];
            \array_push($data,$item);
        }
        return $data;
    }
    /**
     * 更改资源修改log状态
     * 2018-3-16 张煜
     */
    public function setresourcelog($id){
        $log=LogModel::get(["ID"=>$id]);
        if($log){
            $log->Note='';
            $log->save();
        }
    }
    /**
     * 用于获取用户考勤信息
     * 2018-4-7 张煜
     */
    public function getuserlog($id){
        $list=\think\Db::query("select * from log where Note like '%@".$id."@%'");
        return $list;
    }
    /**
     * 用于判断用户是否已签到
     * 2018-4-7 张煜
     */
    public function getset($id,$projectid){
        $list=\think\Db::query("select * from log where Note like '%@".$id."@%'");
        $start=null;
        $end=[];
        $end1=[];
        foreach($list as $value){
            if($value['State']=="签到"){
                $start=$value;
            }else if($value['State']=="签退"){
                array_push($end,$value);
            }else if($value['State']=="登出"){
                array_push($end1,$value);
            }
        }
        if($start!=null){
            if($start['ProjectID']==$projectid){
                if(count($end)==0&&count($end1)==0){
                    return true;
                }else if(count($end)==0){
                    if($start["ID"]>$end1[count($end1)-1]["ID"]){
                        return true;
                    }
                }else if(count($end1)==0){
                    if($start["ID"]>$end[count($end)-1]["ID"]){
                        return true;
                    }
                }else{
                    if($start["ID"]>$end[count($end)-1]["ID"]&&$start["ID"]>$end1[count($end1)-1]["ID"]){
                        return true;
                    }
                }
            }
        }
        return false;
    }
}