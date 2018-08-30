<?php
namespace app\api\controller;
use think\Controller;
use app\api\model\ChatingBox as ChatingBoxModel;
/**
 * 聊天框
 * 部分实装
 * 需要改
 */
class ChatingBox extends Controller{
    /**
     * 通过项目id获取所有聊天内容
     * 已实装
     */
    public function read($projectid){
        $list=ChatingBoxModel::all(['Project'=>$projectid]);
        $data=[];
        foreach ($list as $value) {
            $project=new \app\api\controller\Project();
            $item=[
                'user'=>$value->User,
                'status'=>$value->State,
                'time'=>$value->Time,
                'note'=>$value->Note,
                'projectname'=>$project->getprojectname($value->Project)
            ];
            array_push($data,$item);
        }
        return $data;
    }
    /**
     * 发送聊天
     * 已实装
     */
    public function send($user,$status,$time,$note,$projectid){
        $data=new ChatingBoxModel;
        $data->data([
            'User'  =>$user,
            'State' =>$status,
            'Time'=>$time,
            'Note'=>$note,
            'Project'=>$projectid
        ]);
        $data->save();
    }
    /**
     * 通过idlist获取所有聊天
     */
    public function getallbyprojectid($idlist){
        $data=[];
        foreach($idlist as $value){
            $item=$this->read($value['id']);
            $data=array_merge($data,$item);
        }
        $data=\array_merge($this->read(0),$data);
        return $data;
    }
}