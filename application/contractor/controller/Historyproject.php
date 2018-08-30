<?php
namespace app\contractor\controller;
use think\Controller;
//use think\db;
/**
 * 历史记录详细页面，不过并不想改，等alpha版再说
 * 部分实装
 */
class Historyproject extends Controller
{
    public function index($id)
    {
        $user=new \app\api\controller\Contractors();
        $userid=$user->checkuser($_COOKIE["contractorid"]);
        if($userid){
            $this->assign('face',$user->gethead($userid));
            $project=new \app\api\controller\Project();
            $data=$project->getbasicprojectbyid($id);
            if($data){
                $this->assign('username',$user->getusername($userid));
                $this->assign("project",$data);
                $children=$project->getchildrenproject($id);
                $rating=$project->gethistoryprojectrating($id);
                $this->assign('rating',$rating);
                $this->assign("childrenlist",$children);
                $this->assign('id',$id);
                return $this->fetch('his-project');
            }
            return $this->error("404 未知的历史项目");
        }
        return $this->error("404 未知发包人员");
    }
}
