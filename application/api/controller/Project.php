<?php
namespace app\api\controller;

use app\api\model\Project as ProjectModel;
use think\Controller;

/**
 * 项目数据库操作类
 * 部分实装
 */
class Project extends Controller
{
    private $image = ['11.jpg', '12.jpg', '13.jpg', '14.jpg', '15.jpg', '16.jpg', '17.jpg', '18.jpg', '19.jpg', '20.jpg', '24.jpg', '25.jpg', '26.jpg', '7.jpg', '8.jpg'];
    private $imagenum = 14;
    /**
     * 通过userid获取数据库第一个项目（已过期）
     * 已废弃 已不可使用
     * 不建议使用
     */
    public function getbasicproject($userid)
    { //已过期，不建议使用
        $project = ProjectModel::get(['UserId' => $userid]);
        if ($project) {
            switch ($project->SafetyGrade) {
                case '高':
                    $color = 'high';
                    break;
                case '中':
                    $color = 'mid';
                    break;
                case '低':
                    $color = 'low';
                    break;
                default:
                    $color = '';
                    break;
            }

            $data = [
                'id' => $project->ID,
                'name' => $project->ProjectName,
                'starttime' => $project->ProjectStarttime,
                'endtime' => $project->ProjectEndtime,
                'contractor' => $project->Contractor,
                'safetygrade' => $project->SafetyGrade,
                'plan' => $project->Plan,
                'color' => $color,
            ];
            return $data;
        } else {
            return 0;
        }
    }
    /**
     * 获取项目的结构
     * 服务于日曜图
     * 已实装
     */
    public function getprojectstructuretree($projectid)
    { //获取项目的树形结构图
        $style = [[], ['color' => '#F54F4A'], ['color' => '#27727B'], ['color' => '#B5C334']];
        $project = ProjectModel::get(['ID' => $projectid]);
        if ($project) {
            switch ($project->SafetyGrade) {
                case '高':
                    $color = 1;
                    break;
                case '中':
                    $color = 2;
                    break;
                case '低':
                    $color = 3;
                    break;
                default:
                    $color = 0;
                    break;
            }

            $children = [];
            $list = [];
            $value = 1;
            if ($project->Children) {
                $list = json_decode($project->Children);
                foreach ($list as $item) {
                    $child = $this->getprojectstructuretree($item);
                    array_push($children, $child);
                    $value = $value + $child['value'];
                }
            }
            if ($color != 0) {
                $data = [
                    'name' => $project->ProjectName,
                    'itemStyle' => $style[$color],
                    'value' => $value,
                    'children' => $children,
                    'link' => '/tp5/public/index.php/user/projectdetail/index/id/' . $project->ID,
                ];
                return $data;
            } else {
                $data = [
                    'name' => $project->ProjectName,
                    'itemStyle' => $style[$color],
                    'value' => $value,
                    'children' => $children,
                    'link' => '/tp5/public/index.php/user/projectdetail/index/id/' . $project->ID,
                ];
                return $data;
            }
        }
    }
    /**
     * 获取当前项目的子项目流水瀑布图
     * 已实装
     */
    public function getprojectwaterfall($projectid)
    { //获取项目的流水瀑布图
        $project = ProjectModel::get(['ID' => $projectid]);
        if ($project) {
            $list = $this->getlist($project);
            $data = [];
            $time1 = strtotime(date('Y-m-d') . ' 0:00');
            $time2 = strtotime(date('Y-m-d') . ' 23:59');
            foreach ($list as $value) {
                $starttime = strtotime($value->Starttime);
                $endtime = strtotime($value->Endtime);
                if (($starttime > $time1) and ($endtime < $time2)) {
                    $item = [
                        'name' => $value->ProjectName,
                        'starttime' => $value->Starttime,
                        'endtime' => $value->Endtime,
                        'status' => $value->State,
                        'id' => $value->ID,
                    ];
                    array_push($data, $item);
                }
            }
            return $data;
        }
    }
    /**
     * 获取项目的子项目等待列表
     * 已实装
     */
    public function getprojectwatinglist($projectid)
    { //获取项目等待列表
        $project = ProjectModel::get(['ID' => $projectid]);
        if ($project) {
            $list = $this->getlist($project);
            $data = [];
            $error = [];
            $warning = [];
            $success = [];
            $complete = [];
            foreach ($list as $value) {
                $idlist = explode(',', $value->UserID);
                $username = '';
                foreach ($idlist as $value1) {
                    if ($value1) {
                        $username = $username . (\app\api\model\Users::get(['UserID' => $value1])->UserName) . ' ';
                    }
                }
                $item = [
                    'name' => $value->ProjectName,
                    'user' => $username,
                    'endtime' => $value->Endtime,
                    'status' => $value->State,
                    'level' => $value->SafetyGrade,
                    'id' => $value->ID,
                ];
                switch ($item['status']) {
                    case '延期':
                        $item = array_merge($item, ['color' => 'danger']);
                        array_push($error, $item);
                        break;
                    case '可能延期':
                        $item = array_merge($item, ['color' => 'warning']);
                        array_push($warning, $item);
                        break;
                    case '进度正常':
                    case '完成':
                        $item = array_merge($item, ['color' => 'success']);
                        array_push($success, $item);
                        break;
                    default:
                        $item = array_merge($item, ['color' => 'info']);
                        array_push($complete, $item);
                        break;
                }

            }
            $data = array_merge($error, $warning, $success, $complete);
            return $data;
        }
    }
    /**
     * 获取该项目下的资源列表
     * 已实装
     */
    public function getprojectresource($projectid, $userid, $type = 0)
    { //获取项目下资源列表
        $project = ProjectModel::get(['ID' => $projectid]);
        if ($project) {
            $list = $this->getlist($project);
            $data = [];
            $idlist = [];
            foreach ($list as $value) {
                $id = json_decode($value->Resources);
                foreach ($id as $value) {
                    array_push($idlist, $value);
                }

            }
            if ($type == 0) {
                $user = \app\api\model\Users::get(['UserID' => $userid])->SecurityPermissions;
                $path = 'user';
                $flag='u';
            } else {
                $user = \app\api\model\Contractors::get(['ContractorID' => $userid])->SecurityPermissions;
                $path = 'contractor';
                $flag='c';
            }

            foreach ($idlist as $value) {
                $resource = \app\api\model\Resource::get(['id' => $value]);
                $item = [];
                if($resource->State!='public' && $resource->State!='private' && $resource->State!='等待审核' && $resource->State!='审核未通过' && $resource->State!='已审核'){
                    $list=explode(',',$resource->State);
                    if(\in_array($flag.$userid,$list)){
                        $item = [
                            'name' => $resource->Name,
                            'uploader' => $this->getUserName($resource->Founder),
                            'uploadtime' => $resource->CreationDate,
                            'status' => '可用',
                            'safetygrade' => $resource->SafetyGrade,
                            'link' => '/tp5/public/index.php/' . $path . '/resourcecheck/index/id/' . $resource->ID,
                            'type' => $resource->Type,
                            'color' => 'success',
                            ];
                    }else{
                        $item = [
                            'name' => $resource->Name,
                            'uploader' => $this->getUserName($resource->Founder),
                            'uploadtime' => $resource->CreationDate,
                            'status' => '权限不足',
                            'safetygrade' => $resource->SafetyGrade,
                            'type' => $resource->Type,
                            'link' => '',
                            'color' => 'danger',
                        ];
                    }
                }else{
                    if ($resource->SafetyGrade <= $user) {
                        $item = [
                            'name' => $resource->Name,
                            'uploader' => $this->getUserName($resource->Founder),
                            'uploadtime' => $resource->CreationDate,
                            'status' => '可用',
                            'safetygrade' => $resource->SafetyGrade,
                            'link' => '/tp5/public/index.php/' . $path . '/resourcecheck/index/id/' . $resource->ID,
                            'type' => $resource->Type,
                            'color' => 'success',
                            ];
                    } else {
                        $item = [
                            'name' => $resource->Name,
                            'uploader' => $this->getUserName($resource->Founder),
                            'uploadtime' => $resource->CreationDate,
                            'status' => '权限不足',
                            'safetygrade' => $resource->SafetyGrade,
                            'type' => $resource->Type,
                            'link' => '',
                            'color' => 'danger',
                        ];
                    }
                }
                
                if (!in_array($item, $data)) {
                    array_push($data, $item);
                }
            }
            return $data;
        }
    }
    private function getUserName($id){
        if(strpos($id,'c')!==false){
            $user=new \app\api\controller\Contractors();
            $userid=(explode('c',$id))[1];
            return '发包员 '.$user->getusername($userid);
        }else if(strpos($id,'u')!==false){
            $user=new \app\api\controller\Users();
            $userid=(explode('u',$id))[1];
            return '接包员 '.$user->getusername($userid);
        }
    }
    /**
     * 内部方法 获取改项目下的所有子项目id
     * 已实装
     */
    private function getlist($project)
    { //内部方法，获取所有子项目的id
        if ($project->Children) {
            $list = json_decode($project->Children);
            $data = [$project];
            foreach ($list as $value) {
                $item = ProjectModel::get(['ID' => $value]);
                $child = $this->getlist($item);
                $data = array_merge($data, $child);
            }
            return $data;
        } else {
            return [$project];
        }
    }
    /**
     * 获取该项目的子项目列表
     * 2018-3-7 张煜
     */
    public function getallprojectchildrenlist($id)
    {
        $project = ProjectModel::get(['ID' => $id]);
        $lsit = $this->getlist($project);
        $data = [];
        foreach ($lsit as $value) {
            $username = '';
            $idlist = explode(',', $value->UserID);
            foreach ($idlist as $value1) {
                if ($value1) {
                    $username = $username . (\app\api\model\Users::get(['UserID' => $value1])->UserName) . ' ';
                }
            }
            $item = [
                'name' => $value->ProjectName,
                'username' => $username,
                'endtime' => $value->Endtime,
                'level' => $value->SafetyGrade,
                'status' => $value->State,
                'id' => $value->ID,
            ];
            array_push($data, $item);
        }
        return $data;
    }
    /**
     * 获取该项目及其子项目下的所有接包人员信息
     * 2018-3-7 张煜
     */
    public function getprojectuserlist($id)
    {
        $project = ProjectModel::get(['ID' => $id]);
        $list = $this->getlist($project);
        $useridlist = [];
        $data1 = [];
        $data2 = [];
        $data3 = [];
        foreach ($list as $value) {
            $idlist = explode(',', $value->UserID);
            foreach ($idlist as $value1) {
                if ($value1) {
                    if (in_array($value1, $useridlist) === false) {
                        array_push($useridlist, $value1);
                    }
                }
            }
        }
        $user = new \app\api\controller\Users();
        foreach ($useridlist as $value) {
            $item = $user->getuserworkingstate($value);
            if ($item['busylevel'] == '空闲') {
                array_push($data1, $item);
            } else if ($item['busylevel'] == '正常') {
                array_push($data2, $item);
            } else if ($item['busylevel'] == '繁忙') {
                array_push($data3, $item);
            } else {
                array_push($data1, $item);
            }
        }
        return array_merge($data1, $data2, $data3);
    }

    /**
     * 获取该发包人员手下所有的接包人员信息
     * 2018-3-7 张煜
     */
    public function getallprojectuserlist($id)
    {
        $list = ProjectModel::all(["Contractor" => $id]);
        $useridlist = [];
        $data1 = [];
        $data2 = [];
        $data3 = [];
        foreach ($list as $value) {
            $list = $this->getlist($value);
            foreach ($list as $value) {
                $idlist = explode(',', $value->UserID);
                foreach ($idlist as $value1) {
                    if ($value1) {
                        if (in_array($value1, $useridlist) === false) {
                            array_push($useridlist, $value1);
                        }
                    }
                }
            }
        }
        $user = new \app\api\controller\Users();
        foreach ($useridlist as $value) {
            $item = $user->getuserworkingstate($value);
            if ($item['busylevel'] == '空闲') {
                $item = array_merge($item, ['color' => 'success']);
                array_push($data1, $item);
            } else if ($item['busylevel'] == '正常') {
                $item = array_merge($item, ['color' => 'warning']);
                array_push($data2, $item);
            } else if ($item['busylevel'] == '繁忙') {
                $item = array_merge($item, ['color' => 'danger']);
                array_push($data3, $item);
            } else {
                $item = array_merge($item, ['color' => 'info']);
                array_push($data1, $item);
            }
        }
        return array_merge($data1, $data2, $data3);
    }
    /**
     * 获取项目中所有的人员信息，为当前人员列表
     * 2018-3-7 张煜
     */
    public function getprojectreadylist($id)
    {
        $project = ProjectModel::get(['ID' => $id]);
        $idlist = explode(',', $project->UserID);
        $idlist = array_filter($idlist);
        $user = new \app\api\controller\Users();
        $data = $user->getuserdata($idlist);
        return $data;
    }
    /**
     * 通过id获取项目的基础资料
     * 已实装
     */
    public function getbasicprojectbyid($id)
    { //通过id获取project基础资料
        $project = ProjectModel::get(['ID' => $id]);
        if ($project) {
            $color = '';
            switch ($project->SafetyGrade) {
                case '高':
                    $color = 'high';
                    break;
                case '中':
                    $color = 'mid';
                    break;
                case '低':
                    $color = 'low';
                    break;
                default:
                    $color = '';
                    break;
            }
            $username = '';
            $idlist = explode(',', $project->UserID);
            foreach ($idlist as $value) {
                if ($value) {
                    $username = $username . (\app\api\model\Users::get(['UserID' => $value])->UserName) . ' ';
                }
            }
            $contractor = new \app\api\controller\Contractors();
            $contractorname = $contractor->getusername($project->Contractor);
            $data = [
                'id' => $project->ID,
                'name' => $project->ProjectName,
                'starttime' => $project->Starttime,
                'endtime' => $project->Endtime,
                'user' => $username,
                'safetygrade' => $project->SafetyGrade,
                'plan' => $project->Plan,
                'color' => $color,
                'contractor' => $contractorname,
                'state' => $project->State,
                'grade' => $project->TotalGrade,
            ];
            return $data;
        } else {
            return 0;
        }
    }
    /**
     * 获取所有子项目
     * 已实装
     *
     */
    public function getchildrenproject($projectid)
    { //获取所有子项目
        $data = [];
        $project = ProjectModel::get(['ID' => $projectid]);
        if ($project) {
            if ($project->Children) {
                $list = json_decode($project->Children);
                foreach ($list as $item) {
                    $child = $this->getbasicprojectbyid($item);
                    if ($child != 0) {
                        array_push($data, $child);
                    }
                }
                return $data;
            }

        }
    }

    /**
     * 获取所有项目的延期情况
     * 为发包人员的饼图服务
     * 已实装
     * 需要修改
     */
    public function getprojectpan($userid)
    { //获取所有项目延期情况
        $ontime = ProjectModel::all(['State' => '进度正常', "Contractor" => $userid]);
        $dely0 = ProjectModel::all(['State' => '可能延期', "Contractor" => $userid]);
        $dely = ProjectModel::all(['State' => '延期', "Contractor" => $userid]);
        $other = ProjectModel::all(['State' => '待接收', "Contractor" => $userid]);
        $list = array_merge($ontime, $dely0, $dely);
        foreach ($list as $value) {
            $this->setcompeletegrade($value->ID);
        }
        $data = ['series' => ["data" => [["value" => count($ontime), 'name' => '进度正常'], ["value" => count($dely0), 'name' => '可能延期'], ["value" => count($dely), 'name' => '延期'], ["value" => count($other), 'name' => '待接收']]]];
        return $data;
    }
    /**
     * 获取所有完成的历史项目
     * 已实装
     * 需要修改
     */
    public function gethistoryproject()
    { //获取所有历史项目
        $list = ProjectModel::all(["state" => "完成", "class" => 'root']);
        $data = [];

        foreach ($list as $value) {
            $link = "jumphistory(" . $value->ID . ")";
            $item = [
                "name" => $value->ProjectName,
                "endtime" => $value->ProjectEndtime,
                "img" => "/tp5/public/static/image/" . $value->Cover,
                "link" => $link,
            ];
            array_push($data, $item);
        }
        return $data;
    }
    /**
     * 获取指定userid下的历史记录
     * 已实装
     *
     */
    public function gethistoryprojectbyuserid($userid)
    { //获取指定userid的历史项目
        $list = $this->getprojectbyuserid($userid);
        $data = [];
        foreach ($list as $value) {
            if ($value['State'] == '完成' and $value['Class'] == 'root') {
                $link = "jumphistory(" . $value['ID'] . ")";
                $item = [
                    "name" => $value['ProjectName'],
                    "endtime" => $value['Endtime'],
                    "img" => "/tp5/public/static/image/" . $value['Cover'],
                    "link" => $link,
                ];
                array_push($data, $item);
            }
        }
        return $data;
    }
    /**
     * 获取所有等待人员分配的项目
     * 已实装
     * 需要修改
     */
    public function getwaitinguserproject($userid)
    { //获取等待人员分配的project
        $list = ProjectModel::all(["state" => "等待人员", "Contractor" => $userid]);
        $data = [];
        foreach ($list as $value) {
            $link = "jumpaddproject(" . $value->ID . ")";
            $item = [
                "name" => $value->ProjectName,
                "endtime" => $value->Endtime,
                "img" => "/tp5/public/static/image/" . $value->Cover,
                "link" => $link,
            ];
            array_push($data, $item);
        }
        return $data;
    }
    /**
     * 获取所有正在进行的项目
     * 已实装
     * 需要修改
     */
    public function getworkingproject($userid)
    { //获取正在进行中的project
        $list3 = ProjectModel::all(["state" => "进度正常", "Contractor" => $userid]);
        $list2 = ProjectModel::all(["state" => "可能延期", "Contractor" => $userid]);
        $list1 = ProjectModel::all(["state" => "延期", "Contractor" => $userid]);
        $list4 = ProjectModel::all(["state" => "待接收", "Contractor" => $userid]);
        $data = [];
        $list = array_merge($list1, $list2, $list3, $list4);
        foreach ($list as $value) {
            $link = "jumpaddproject(" . $value->ID . ")";
            $item = [
                "name" => $value->ProjectName,
                "endtime" => $value->Endtime,
                "img" => "/tp5/public/static/image/" . $value->Cover,
                "link" => $link,
            ];
            array_push($data, $item);
        }
        return $data;
    }
    /**
     * 获取延期项目
     * 2018-3-16 张煜
     */
    public function getdelayproject($userid)
    {
        $list = ProjectModel::all(["state" => "延期", "Contractor" => $userid]);
        $data = [];
        foreach ($list as $value) {
            $link = "jumpaddproject(" . $value->ID . ")";
            $item = [
                "name" => $value->ProjectName,
                "endtime" => $value->Endtime,
                "img" => "/tp5/public/static/image/" . $value->Cover,
                "link" => $link,
            ];
            array_push($data, $item);
        }
        return $data;
    }
    /**
     * 获取可能延期项目
     * 2018-3-16 张煜
     */
    public function getbusyproject($userid)
    {
        $list = ProjectModel::all(["state" => "可能延期", "Contractor" => $userid]);
        $data = [];
        foreach ($list as $value) {
            $link = "jumpaddproject(" . $value->ID . ")";
            $item = [
                "name" => $value->ProjectName,
                "endtime" => $value->Endtime,
                "img" => "/tp5/public/static/image/" . $value->Cover,
                "link" => $link,
            ];
            array_push($data, $item);
        }
        return $data;
    }
    /**
     * 获取进度正常项目
     * 2018-3-16 张煜
     */
    public function getontimeproject($userid)
    {
        $list = ProjectModel::all(["state" => "进度正常", "Contractor" => $userid]);
        $data = [];
        foreach ($list as $value) {
            $link = "jumpaddproject(" . $value->ID . ")";
            $item = [
                "name" => $value->ProjectName,
                "endtime" => $value->Endtime,
                "img" => "/tp5/public/static/image/" . $value->Cover,
                "link" => $link,
            ];
            array_push($data, $item);
        }
        return $data;
    }
    /**
     * 获取所有等待资源分配的项目
     * 已实装
     * 需要修改
     *
     */
    public function getwaitingresourceproject($userid)
    { //获取等待资源分配的project
        $list = ProjectModel::all(["State" => "等待资源", "Contractor" => $userid]);
        $data = [];
        foreach ($list as $value) {
            $link = "jumpaddproject(" . $value->ID . ")";
            $item = [
                "name" => $value->ProjectName,
                "endtime" => $value->Endtime,
                "img" => "/tp5/public/static/image/" . $value->Cover,
                "link" => $link,
            ];
            array_push($data, $item);
        }
        return $data;
    }
    /**
     * 获取此id下的所有项目
     */
    public function getprojectbyuserid($id)
    { //获取含有此userid的所有项目
        $data = \think\Db::query("select * from project where UserID like '%," . $id . ",%'");
        return $data;
    }
    /**
     * 获取该userid下所有等待确认的项目
     * 已实装
     */
    public function getuserwaitingprojectbyuserid($userid)
    { //获取等待接包人员确认接收的项目
        $list = $this->getprojectbyuserid($userid);
        $data = [];
        foreach ($list as $value) {
            if ($value['State'] == '待接收') {
                $link = "jumpprojectdetail(" . $value['ID'] . ")";
                $item = [
                    "name" => $value['ProjectName'],
                    "endtime" => $value['Endtime'],
                    "img" => "/tp5/public/static/image/" . $value['Cover'],
                    "link" => $link,
                ];
                array_push($data, $item);
            }
        }
        return $data;
    }
    /**
     * 获取此userid下所有正在进行的项目
     * 已实装
     */
    public function getuserworkingprojectbyuserid($userid)
    { //获取含此userid的正在进行的项目
        $list = $this->getprojectbyuserid($userid);
        $list1 = [];
        $list2 = [];
        $list3 = [];
        $data = [];
        foreach ($list as $value) {
            $link = "jumpprojectdetail(" . $value['ID'] . ")";
            $item = [
                "name" => $value['ProjectName'],
                "endtime" => $value['Endtime'],
                "img" => "/tp5/public/static/image/" . $value['Cover'],
                "link" => $link,
            ];
            if ($value['State'] == '进度正常') {
                array_push($list3, $item);
            } elseif ($value['State'] == '可能延期') {
                array_push($list2, $item);
            } elseif ($value['State'] == '延期') {
                array_push($list1, $item);
            }
        }
        $data = array_merge($list1, $list2, $list3);
        return $data;
    }
    /**
     * 通过id设置项目接包人员id
     * 已实装
     */
    public function setprojectuserid($id, $idlist)
    { //设置此id的project人员id
        $data = ',';
        foreach ($idlist as $value) {
            $data = $data . $value . ',';
        }
        $project = ProjectModel::get(["ID" => $id]);
        $project->UserID = $data;
        $project->save();
    }
    /**
     * 添加新的项目
     * 已实装
     */
    public function addnewproject($contractor, $starttime, $endtime, $safetygrade, $projectname, $class, $plan, $resultnum, $type = 1, $price)
    { //添加新project
        $project = new ProjectModel();
        $user = new \app\api\controller\Contractors();
        $contractorid = $user->checkuser($contractor);
        $project->data([
            "Contractor" => $contractorid,
            "Starttime" => $starttime,
            "Endtime" => $endtime,
            "SafetyGrade" => $safetygrade,
            "ProjectName" => $projectname,
            "Class" => $class,
            "Plan" => $plan,
            "ResultResourceNum" => $resultnum,
            "State" => '等待人员',
            "Children" => "{}",
            "Price" => $price,
            "Cover"=>$this->image[rand(0, $this->imagenum)]
        ]);
        $project->save();
        if ($type) {
            return json(["status" => 1]);
        }
        return $project->ID;
    }
    /**
     * 为项目添加新的子项目
     * 已时装
     * 2018-3-7 张煜
     */
    public function addnewchildproject($fatherproject, $contractor, $starttime, $endtime, $safetygrade, $projectname, $class, $plan, $resultnum, $price)
    {
        $child = ($this->addnewproject($contractor, $starttime, $endtime, $safetygrade, $projectname, $class, $plan, $resultnum, 0, $price));
        $this->addprojectchild($fatherproject, [$child]);
        return json(['status' => 1]);
    }
    /**
     * 为项目设置子项目id（覆盖）
     * 已实装
     * 2018-3-7 张煜
     */
    public function setprojcetchild($id, $children)
    {
        $project = ProjectModel::get(["ID" => $id]);
        $project->Children = json_encode($children);
        $project->save();
    }
    /**
     * 为项目添加子项目id（不覆盖）
     * 已实装
     * 2018-3-7 张煜
     */
    public function addprojectchild($id, $children)
    {
        $project = ProjectModel::get(["ID" => $id]);
        $list = json_decode($project->Children);
        $data = [];
        foreach ($list as $value) {
            array_push($data, $value);
        }
        $data = array_merge($data, $children);
        $project->Children = json_encode($data);
        $project->save();
    }
    /**
     * 通过项目id为项目设置项目资源id（覆盖）
     * 已实装
     */
    public function setprojectresourceid($id, $resourceidlist)
    { //设置resourceid
        $project = ProjectModel::get(["ID" => $id]);
        $project->Resources = json_encode($resourceidlist);
        $project->save();
    }
    /**
     * 通过项目id为项目添加资源（不覆盖）
     * 已实装
     */
    public function addprojectresourceid($id, $resourceidlist)
    { //为指定项目添加resource
        $project = ProjectModel::get(["ID" => $id]);
        if ($project->State == '等待资源') {
            $this->setprojectresourceid($id, $resourceidlist);
            $project->State = '待接收';
            $project->save();
            return 1;
        } else {
            $list = json_decode($project->Resources);
            $data = [];
            foreach ($list as $value) {
                array_push($data, $value);
            }
            $data = array_merge($data, $resourceidlist);
            $project->Resources = json_encode($data);
            $project->save();
            return 2;
        }
        return 0;
    }
    /**
     * 通过id清除项目
     * 已实装
     */
    public function delete($id)
    { //清除指定的项目
        $project = ProjectModel::get(["ID" => $id]);
        if ($project) {
            $project->delete();
            return json(["status" => 1]);
        }
        return json(['status' => 0]);
    }
    /**
     * 获取项目的雷达图
     * 已实装
     */
    public function getradarmap($id)
    { //获取指定项目的完成雷达图
        $projectlist = ProjectModel::all(["State" => '完成']);
        $project = ProjectModel::get(["ID" => $id]);
        $maxprice = 25000;
        $totalgrade = 0;
        $price = 0;
        $contractorGrade = 0;
        $responseSpeed = 0;
        $completeGrade = 0;
        $implementationGrade = 0;
        $key = 0;
        foreach ($projectlist as $key => $value) {
            $price += $value->Price;
            if ($maxprice < $value->Price) {
                $maxprice = $value->Price;
            }
            $contractorGrade += $value->ContractorGrade;
            $responseSpeed += $value->ResponseSpeed;
            $completeGrade += $value->CompleteGrade;
            $implementationGrade += $value->ImplementationGrade;
            $totalgrade += $value->TotalGrade;
        }
        $key++;
        $avg = [
            $totalgrade / $key,
            $contractorGrade / $key,
            $completeGrade / $key,
            $implementationGrade / $key,
            $responseSpeed / $key,
            $price / $key,
        ];
        $now = [
            $project->TotalGrade,
            $project->ContractorGrade,
            $project->CompleteGrade,
            $project->ImplementationGrade,
            $project->ResponseSpeed,
            $project->Price,
        ];
        $data = [
            "indicator" => [
                [
                    "name" => "项目总评",
                    "max" => 100,
                ],
                [
                    "name" => "人员总评",
                    "max" => 100,
                ],
                [
                    "name" => "完成度",
                    "max" => 100,
                ],
                [
                    "name" => "执行度",
                    "max" => 100,
                ],
                [
                    "name" => "反馈速度",
                    "max" => 100,
                ],
                [
                    "name" => "市场价值创造",
                    "max" => $maxprice,
                ],
            ],
            "series" => [
                [
                    "data" => [
                        [
                            "value" => $now,
                            "name" => "当前项目",
                            "itemStyle"=>[
                                "normal"=>[
                                    "color"=>"#009900"
                                ]
                            ]
                        ],
                        [
                            "value" => $avg,
                            "name" => "平均",
                            "itemStyle"=>[
                                "normal"=>[
                                    "color"=>"#ff9900"
                                ]
                            ]
                        ],
                    ],
                ],
            ],
        ];
        return $data;
    }
    /**
     * 为项目添加成果的id（覆盖）
     * 已实装
     */
    public function setresultresourceid($id, $resourceidlist)
    { //添加成果id
        $project = ProjectModel::get(["ID" => $id]);
        $list = json_decode($project->ResultResourceID);
        $list = array_merge($list, $resourceidlist);
        $project->ResultResourceID = json_encode($list);
        $project->save();
        $this->setcompeletegrade($id);
    }
    /**
     * 设置项目的完成度并设置项目的状态（进度正常、可能延期、延期）
     * 已实装
     * 需要修改
     * 等alpha版
     */
    public function setcompeletegrade($id)
    { //设置项目完成度，并设置项目状态
        $project = ProjectModel::get(["ID" => $id]);
        $list = json_decode($project->ResultResourceID);
        $now = count($list);
        $completeGrade = round(($now / $project->ResultResourceNum) * 100);
        $project->CompleteGrade = $completeGrade;
        $now = strtotime(date('Y-m-d H:i'));
        $start = strtotime($project->Starttime);
        $end = strtotime($project->Endtime);
        if (($now - $end) > 0) {
            $project->State = '延期';

        } elseif (round((($end - $now) / ($end - $start)) * 100) < (100 - $completeGrade)) {
            $project->State = '可能延期';
        } else {
            $project->State = '进度正常';
        }
        $project->save();
    }
    /**
     * 获取项目详情中的仪表盘数据
     * 已实装
     * 需要修改
     * 等alpha版
     */
    public function getspeederdata($id)
    {
        $project = ProjectModel::get(["ID" => $id]);
        $this->setcompeletegrade($id);
        $safetygrade = 1;
        $state = 0;
        switch ($project->SafetyGrade) {
            case '高':
                $safetygrade = 2;
                break;
            case '中':
                $safetygrade = 1;
                break;
            case '低':
                $safetygrade = 0;
                break;
            default:
                # code...
                break;
        }
        switch ($project->State) {
            case '进度正常':
                $state = 0;
                break;
            case '可能延期':
                $state = 1;
                break;
            case '延期':
                $state = 2;
                break;
            default:
                # code...
                break;
        }
        $data = [
            "series" => [
                [
                    'name' => '项目完成率',
                    'data' => [
                        [
                            'value' => $project->CompleteGrade,
                            'name' => '完成率',
                        ],
                    ],
                ],
                [
                    'name' => '安全等级',
                    'data' => [
                        [
                            'value' => $safetygrade,
                            'name' => '等级',
                        ],
                    ],
                ],
                [
                    'name' => '状态',
                    'data' => [
                        [
                            'value' => $state,
                            'name' => '状态',
                        ],
                    ],
                ],
            ],
        ];
        return $data;
    }
    /**
     * 确认项目
     * 未实装
     * 2018-3-5 张煜
     */
    public function accept($id)
    {
        $this->acceptchild($id);
        return json(["status" => 1]);
    }
    private function acceptchild($id)
    {
        $project = ProjectModel::get(["ID" => $id]);
        if ($project) {
            $list = \json_decode($project->Children);
            if ($list) {
                foreach ($list as $value) {
                    $this->acceptchild($value);
                }
            }
            $project->State = '完成';
            $project->ProjectEndtime = date('Y-m-d H:i');
            $project->save();
        }
    }
    /**
     * 检查项目下是否有项目需要验收
     * 2018-3-25 张煜
     */
    public function acceptcheck($id)
    {
        $project = ProjectModel::get(["ID" => $id]);
        $list = \json_decode($project->Children);
        if ($list) {
            foreach ($list as $value) {
                if ($this->checkaccept($value) == 0) {
                    return json(['status' => 0]);
                }
            }
        }
        return json(['status' => 1]);
    }
    private function checkaccept($id)
    {
        $project = ProjectModel::get(["ID" => $id]);
        if ($project->State != "完成") {
            return 0;
        }
        $list = \json_decode($project->Children);
        if ($list) {
            foreach ($list as $value) {
                if ($this->checkaccept($value) == 0) {
                    return 0;
                }
            }
        }
        return 1;
    }
    /**
     * 获取该发包员的项目列表
     * 2018-3-6 张煜
     */
    public function getprojectlist($id)
    {
        $user = new \app\api\controller\Contractors();
        $userid = $user->checkuser($id);
        $list = ProjectModel::all(["Contractor" => $userid]);
        $data1 = [];
        $data2 = [];
        $data3 = [];
        $data4 = [];
        foreach ($list as $value) {
            $username = '';
            $idlist = explode(',', $value->UserID);
            foreach ($idlist as $value1) {
                if ($value1) {
                    $username = $username . (\app\api\model\Users::get(['UserID' => $value1])->UserName) . ' ';
                }
            }
            switch ($value->State) {
                case '延期':
                    $item = [
                        'name' => $value->ProjectName,
                        'username' => $username,
                        'endtime' => $value->Endtime,
                        'level' => $value->SafetyGrade,
                        'status' => $value->State,
                        'id' => $value->ID,
                        'color' => 'danger',
                    ];
                    array_push($data1, $item);
                    break;
                case '等待人员':
                case '等待资源':
                case '可能延期':
                    $item = [
                        'name' => $value->ProjectName,
                        'username' => $username,
                        'endtime' => $value->Endtime,
                        'level' => $value->SafetyGrade,
                        'status' => $value->State,
                        'id' => $value->ID,
                        'color' => 'warning',
                    ];
                    array_push($data2, $item);
                    break;
                case '待接收':
                    $item = [
                        'name' => $value->ProjectName,
                        'username' => $username,
                        'endtime' => $value->Endtime,
                        'level' => $value->SafetyGrade,
                        'status' => $value->State,
                        'id' => $value->ID,
                        'color' => 'info',
                    ];
                    array_push($data3, $item);
                    break;
                case '进度正常':
                case '完成':
                    $item = [
                        'name' => $value->ProjectName,
                        'username' => $username,
                        'endtime' => $value->Endtime,
                        'level' => $value->SafetyGrade,
                        'status' => $value->State,
                        'id' => $value->ID,
                        'color' => 'success',
                    ];
                    array_push($data4, $item);
                    break;
            }

        }
        $data = array_merge($data1, $data2, $data3, $data4);
        return $data;
    }
    /**
     * 获取等待操作的项目
     */
    public function getcontrollist($id)
    {
        $waitinguser = ProjectModel::all(['State' => '等待人员', 'Contractor' => $id]);
        $waitingresource = ProjectModel::all(['State' => '等待资源', 'Contractor' => $id]);
        $data = [];
        if ($waitinguser) {
            foreach ($waitinguser as $value) {
                $item = [
                    'name' => '等待人员',
                    'poster' => '系统',
                    'time' => date('Y-m-d H:i'),
                    'project' => $value->ProjectName,
                    'level' => '中',
                    'type' => '操作',
                    'note' => $value->ProjectName . ' 等待添加人员',
                    'link' => '/tp5/public/index.php/contractor/adduserdetail/index/id/' . $value->ID,
                    'color' => 'warning',
                ];
                array_push($data, $item);
            }
        }
        if ($waitingresource) {
            foreach ($waitingresource as $value) {
                $item = [
                    'name' => '等待资源',
                    'poster' => '系统',
                    'time' => date('Y-m-d H:i'),
                    'project' => $value->ProjectName,
                    'level' => '中',
                    'type' => '操作',
                    'note' => $value->ProjectName . ' 等待添加资源',
                    'link' => '/tp5/public/index.php/contractor/addresourcedetail/index/id/' . $value->ID,
                    'color' => 'warning',
                ];
                array_push($data, $item);
            }
        }
        return $data;
    }
    public function getprojectname($id)
    {
        if ($id) {
            return (ProjectModel::get(['ID' => $id]))->ProjectName;
        }
        return "System";
    }

    public function addprojectuserid($id, $userid)
    {
        $project = ProjectModel::get(['ID' => $id]);
        if ($project) {
            $project->UserID = $project->UserID . $userid;
            if ($project->State == '等待人员') {
                $project->State = '等待资源';
                $project->save();
                return 1;
            }
            $project->save();
            return 2;
        }
    }

    public function getprojectresourcereadylist($id)
    {
        $project = ProjectModel::get(['ID' => $id]);
        $list = json_decode($project->Resources);
        $data = [];
        foreach ($list as $value) {
            $resource = \app\api\model\Resource::get(['ID' => $value]);
            $item = [
                'id' => $resource->ID,
                'name' => $resource->Name,
                'uploader' => $this->getUserName($resource->Founder),
                'uploadtime' => $resource->CreationDate,
                'editor' => $this->getUserName($resource->Editor),
                'modifieddate' => $resource->ModifiedDate,
                'safetygrade' => $resource->SafetyGrade,
                'type' => $resource->Type,
            ];
            array_push($data, $item);
        }
        return $data;
    }
    public function gethistoryprojectrating($id)
    {
        $project = ProjectModel::get(['ID' => $id]);
        if ($project) {
            $data = [
                'totalgrade' => $project->TotalGrade,
                'contractorgrade' => $project->ContractorGrade,
                'completegrade' => $project->CompleteGrade,
                'implementationgrade' => $project->ImplementationGrade,
                'responsespeed' => $project->ResponseSpeed,
                'price' => $project->Price,
            ];
            return $data;
        }
    }
    /**
     * 通过确认获取项目
     * 2018-3-8 袁宜照
     *
     */
    public function confirmProject($id)
    {
        $project = ProjectModel::get(['ID' => $id]);
        $back = [];
        if ($project) {
            $project->save(['State' => '进度正常']);
            $back = ['status' => 1];
            return json($back);
        }
        return json(['status' => 0]);
    }
    /**
     * 获取发包人员id
     * 2018-3-9 张煜
     */
    public function getcontractorid($id)
    {
        $project = ProjectModel::get(['ID' => $id]);
        if ($project) {
            return $project->Contractor;
        }
    }
    /**
     * 添加项目成果id(不覆盖)
     * 2018-3-9 张煜
     */
    public function addprojectresultid($id, $resource)
    {
        $project = ProjectModel::get(['ID' => $id]);
        $data = [];
        $list = json_decode($project->ResultResourceID);
        foreach ($list as $value) {
            array_push($data, $value);
        }
        array_push($data, $resource);
        $project->ResultResourceID = json_encode($data);
        $project->save();
        $this->setcompeletegrade($id);
        //$log=new \app\api\controller\Log();
        //$log->addresultupload($id);
    }
    /**
     * 签到并开始工作
     * 2018-3-9袁宜照
     */
    public function checkAndWork($proid)
    {
        $log = new \app\api\model\Log();
        if (\think\Cookie::get('userid')) {
            $us = new \app\api\controller\Users();
            $cookie = \think\Cookie::get('userid');
            $userid = $us->checkuser($cookie);
            $user = \app\api\model\Users::get(['UserID' => $userid]);
        }
        if ($cookie) {
            if ($user) {
                \app\api\controller\Log::addLog($proid, $user->UserName, $userid);
                $user->OnlineStatus = 2;
                $user->save();
                return json(['state' => '1']);
            } else {
                return json(['state' => '0']);
            }

        } else {
            return json(['state' => '-1']);
        }

    }
    /**
     * 获取该project 的resultid
     * 2018-3-16 张煜
     */
    public function getprojectresultid($id)
    {

    }
    /**
     * 获取该userid下的所有project result id
     * 2018-3-16 张煜
     */
    public function getprojectresultidbycontractorid($userid)
    {
        $list = ProjectModel::all(["Contractor" => $userid]);
        $data = [];

        foreach ($list as $value) {
            $resultlist = \json_decode($value->ResultResourceID);
            foreach ($resultlist as $value1) {
                array_push($data, \app\api\model\Resource::get(["ID" => $value1]));
            }
        }
        return $data;
    }
    /**
     * 获取结果
     * 2018-3-16 张煜
     */
    public function getresultlist($id)
    {
        $project = ProjectModel::get(["ID" => $id]);
        $resource = new \app\api\controller\Resource();
        if ($project) {
            $list = \json_decode($project->ResultResourceID);
            $data = [];
            foreach ($list as $value) {
                $item = $resource->getresource($value);
                if ($item) {
                    if ($item['state'] == "已审核") {
                        $color = 'success';
                        switch ($item['type']) {
                            case "计划书":
                                $color = 'info';
                                break;
                        }
                        $state = '已审核';
                    } else if ($item['state'] == "等待审核") {
                        $color = 'warning';
                        $state = '等待审核';
                    } else if ($item['state'] == "审核未通过") {
                        $color = 'danger';
                        $state = '审核未通过';
                    } else {
                        $color = '';
                        $state = '未知';
                    }
                    $item = array_merge($item, ['color' => $color, 'state' => $state, 'link' => '/tp5/public/index.php/contractor/resourcecheck/resultcheck/id/' . $item['id']]);
                    array_push($data, $item);
                }
            }
            return $data;
        }
    }
    public function getresultcontrollist($id, $userid)
    {
        $project = ProjectModel::get(["ID" => $id]);
        $resource = new \app\api\controller\Resource();
        if ($project) {
            $list = \json_decode($project->ResultResourceID);
            $data = [];
            foreach ($list as $value) {
                $item = $resource->getresource($value);
                if ($item) {
                    if ($item['uploader'] == 'u' . $userid) {
                        if ($item['state'] == "已审核") {
                            $color = 'success';
                            switch ($item['type']) {
                                case "计划书":
                                    $color = 'info';
                                    break;
                            }
                            $state = '已审核';
                        } else if ($item['state'] == "等待审核") {
                            $color = 'warning';
                            $state = '等待审核';
                        } else if ($item['state'] == "审核未通过") {
                            $color = 'danger';
                            $state = '审核未通过';
                        } else {
                            $color = '';
                            $state = '未知';
                        }
                        $item = array_merge($item, ['color' => $color, 'state' => $state, 'link' => '/tp5/public/index.php/contractor/resourcecheck/resultcheck/id/' . $item['id']]);
                        array_push($data, $item);
                    }
                }
            }
            return $data;
        }

    }
    /**
     * 检查是否有此人在该项目中
     * 2018-3-17 张煜
     */
    public function checkuser($userid, $id)
    {
        if ($userid) {
            $project = ProjectModel::get(["ID" => $id]);
            if ($project) {
                $idlist = explode(',', $project->UserID);
                if (\in_array($userid, $idlist)) {
                    return 1;
                }
            }
        }
        return 0;
    }
    /**
     * 设置项目评分
     * 2018-3-21 张煜
     * 2018-4-30 修改
     */
    public function setprojectrank($id, $implementationgrade, $totalgrade, $qualitygrade, $usergrade)
    {
        $project = ProjectModel::get(['ID' => $id]);
        if ($project) {
            $list = explode(',', $project->UserID);
            $user = new \app\api\controller\Users();
            foreach ($list as $value) {
                if ($value) {
                    $user->addprice($value, $project->Price);
                    $creditrating = $usergrade;
                    $grade=$implementationgrade*0.6+$totalgrade*0.2+$qualitygrade*0.2;
                    $user->setgrade($value, $grade, $creditrating);
                }
            }
            $project->data([
                'TotalGrade' => $totalgrade,
                'ContractorGrade' => $usergrade,
                'ResponseSpeed' => $qualitygrade,
                'ImplementationGrade' => $implementationgrade,
            ]);
            $project->save();
        }
    }
    /**
     * 获取该接包人员所进行的项目
     */
    public function getuserreadyproject($userid)
    {
        $list = $this->getprojectbyuserid($userid);
        $data = [];
        foreach ($list as $value) {
            if ($value['State'] == '完成') {
                $item = [
                    'name' => $value['ProjectName'],
                    'starttime' => $value['Starttime'],
                    'safetygrade' => $value['SafetyGrade'],
                    'state' => '完成',
                    'grade' => $value['TotalGrade'],
                    'id' => $value['ID'],
                ];
                array_push($data, $item);
            }
        }
        return $data;
    }
    public function getresultupload($userid)
    {
        $list = ProjectModel::all(["Contractor" => $userid]);
        $data = [];
        foreach ($list as $value) {
            $resultlist = json_decode($value->ResultResourceID);
            foreach ($resultlist as $resultid) {
                $result = \app\api\model\Resource::get(["ID" => $resultid]);
                if ($result) {
                    if ($result->State == "等待审核") {
                        $item = [
                            'name' => '成果审核',
                            'poster' => '系统',
                            'time' => date('Y-m-d H:i'),
                            'project' => $value->ProjectName,
                            'level' => '中',
                            'type' => '操作',
                            'note' => $value->ProjectName . ' 等待成果审核',
                            'link' => '/tp5/public/index.php/contractor/resourcecheck/resultcheck/id/' . $result->ID,
                            'color' => 'warning',
                        ];
                        array_push($data, $item);
                    }
                }
            }
        }
        return $data;
    }
    public function getallprojectlist($userid){
        $list = ProjectModel::all(["Contractor" => $userid]);
        $data = [];
        foreach ($list as $value) {
            if($value->State!='完成'){
                $item=[
                    'name'=>$value->ProjectName,
                    'id'=>$value->ID
                ];
                array_push($data,$item);
            }
        }
        return $data;
    }
    public function deleteprojectuserid($id,$userid){
        $project=ProjectModel::get(['ID'=>$id]);
        if($project){
            $list=explode(',',$project->UserID);
            $list=array_diff($list,[$userid]);
            $back=',';
            foreach($list as $value){
                if($value){
                    $back=$back.$value.',';
                }
            }
            $project->UserID=$back;
            $project->save();
            return 1;
        }
        return 0;
    }
    public function deleteprojectresourceid($id,$resourceid){
        $project=ProjectModel::get(['ID'=>$id]);
        if($project){
            $list=\json_decode($project->Resources);
            $list=array_diff($list,[$resourceid]);
            $project->Resources=json_encode($list);
            $project->save();
            return 1;
        }
        return 0;
    }
}
