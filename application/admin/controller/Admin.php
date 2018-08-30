<?php
namespace app\admin\controller;

use app\api\model\Companies as CompanyModel;
use app\api\model\Project as ProjectModel;
use app\api\model\Users as UserModel;
use db;
use think\Controller;

class Admin extends Controller
{
    
    public function index()
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $sysos = $_SERVER["SERVER_SOFTWARE"]; //获取服务器标识的字串
            $sysversion = PHP_VERSION; //获取PHP服务器版本
            //以下两条代码连接MySQL数据库并获取MySQL数据库版本信息
            $connect = mysqli_connect("localhost", "root", "zhangyuk");
            $mysqlinfo = mysqli_get_server_info($connect);
            //从服务器中获取GD库的信息
            if (function_exists("gd_info")) {
                $gd = gd_info();
                $gdinfo = $gd['GD Version'];
            } else {
                $gdinfo = "未知";
            }
            //从GD库中查看是否支持FreeType字体
            $freetype = $gd["FreeType Support"] ? "支持" : "不支持";
            //从PHP配置文件中获得是否可以远程文件获取
            $allowurl = ini_get("allow_url_fopen") ? "支持" : "不支持";
            //从PHP配置文件中获得最大上传限制
            $max_upload = ini_get("file_uploads") ? ini_get("upload_max_filesize") : "Disabled";
            //从PHP配置文件中获得脚本的最大执行时间
            $max_ex_time = ini_get("max_execution_time") . "秒";
            //以下两条获取服务器时间，中国大陆采用的是东八区的时间,设置时区写成Etc/GMT-8
            date_default_timezone_set("Etc/GMT-8");
            $systemtime = date("Y-m-d H:i:s", time());

            $this->assign('sysos', $sysos);
            $this->assign('sysversion', $sysversion);
            $this->assign('mysqlinfo', $mysqlinfo);
            $this->assign('gdinfo', $gdinfo);
            $this->assign('freetype', $freetype);
            $this->assign('allowurl', $allowurl);
            $this->assign('max_upload', $max_upload);
            $this->assign('max_ex_time', $max_ex_time);
            $this->assign('systemtime', $systemtime);
            $this->assign('username', $user->getusername($userid));
            //\app\api\controller\Server::getStatus();
            return $this->fetch('server');
        }
        //\think\Log::record('未授权的管理员登陆');
        return $this->error('404 未知管理员');
    }

    public function reviseuser()
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $this->assign('username', $user->getusername($userid));
            $id = input('id');
            $list = UserModel::find($id);
            $companyid = new \app\api\controller\Companies();
            $advantage = '';
            foreach (json_decode($list['Advantage']) as $value) {
                $advantage = $advantage . $value . " ";
            };
            $li = [
                'UserID' => $list->UserID,
                'UserName' => $list->UserName,
                'Gender' => $list->Gender,
                'Birthday' => $list->Birthday,
                'PhoneNumber' => $list->PhoneNumber,
                'Advantage' => $advantage,
                'Company' => $companyid->getcompanyname($list->Company),
                'EmergencyContactName' => $list->EmergencyContactName,
                'EmergencyContactPhone' => $list->EmergencyContactPhone,
                'CreditRating' => $list->CreditRating,
                'SecurityPermissions' => $list->SecurityPermissions,
            ];
            if (request()->isPost()) {
                $companyid = new \app\api\controller\Companies();
                $advantagelist = explode(" ", input('Advantage'));
                $data = [
                    'UserID' => input('id'),
                    'UserName' => input('UserName'),
                    'Gender' => input('Gender'),
                    'Birthday' => input('Birthday'),
                    'PhoneNumber' => input('PhoneNumber'),
                    'Advantage' => json_encode($advantagelist),
                    'Company' => $companyid->getcompanyid(input('Company')),
                    'EmergencyContactName' => input('EmergencyContactName'),
                    'EmergencyContactPhone' => input('EmergencyContactPhone'),
                    'CreditRating' => input('CreditRating'),
                    'SecurityPermissions' => input('SecurityPermissions'),
                ];
                $save = UserModel::update($data);
                if ($save !== false) {
                    $this->success('修改成功！', 'admin/user');
                } else {
                    $this->error('修改失败！');
                }
                return;
            };
            $this->assign('list', $li);
            return $this->fetch('revise-user');
        }
        //\think\Log::record('未授权的管理员登陆');
        return $this->error('404 未知管理员');
    }

    public function revisecon()
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $this->assign('username', $user->getusername($userid));
            $id = input('id');
            $list = db('contractor')->find($id);
            if (request()->isPost()) {
                $data = [
                    'ContractorID' => input('id'),
                    'UserName' => input('UserName'),
                    'Gender' => input('Gender'),
                    'Birthday' => input('Birthday'),
                    'Nation' => input('Nation'),
                    'PhoneNumber' => input('PhoneNumber'),
                    'EmergencyContactName' => input('EmergencyContactName'),
                    'EmergencyContactPhone' => input('EmergencyContactPhone'),
                    'SecurityPermissions' => input('SecurityPermissions'),
                ];
                $save = db('contractor')->update($data);
                if ($save !== false) {
                    $this->success('修改成功！', 'admin/contractors');
                } else {
                    $this->error('修改失败！');
                }
                return;
            }
            $this->assign('list', $list);
            return $this->fetch('revise-con');
        }
        //\think\Log::record('未授权的管理员登陆');
        return $this->error('404 未知管理员');
    }

    public function revisecom()
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $this->assign('username', $user->getusername($userid));
            $id = input('id');
            $li = CompanyModel::get(["ID" => $id]);
            $advantage = '';
            foreach (json_decode($li->Advantage) as $value) {
                $advantage = $advantage . $value . " ";
            }
            $list = [
                "ID" => $li->ID,
                "Name" => $li->Name,
                "SecurityPermissions" => $li->SecurityPermissions,
                "CreditRating" => $li->CreditRating,
                "Advantage" => $advantage,
                "Document" => $li->Document,
                "MembersNumber" => $li->MembersNumber,
                "BankCardNumber" => $li->BankCardNumber,
                "PhoneNumber" => $li->PhoneNumber,
                "PrincipalName" => $li->PrincipalName,
            ];
            if (request()->isPost()) {
                $advantagelist = explode(" ", input('Advantage'));
                $data = [
                    'ID' => input('id'),
                    'Name' => input('Name'),
                    'PhoneNumber' => input('PhoneNumber'),
                    'Advantage' => json_encode($advantagelist),
                    'CreditRating' => input('CreditRating'),
                    'BankCardNumber' => input('BankCardNumber'),
                    'SecurityPermissions' => input('SecurityPermissions'),
                    'PrincipalName' => input('PrincipalName'),
                ];
                $save = db('companies')->update($data);
                if ($save !== false) {
                    $this->success('修改成功！', 'admin/companies');
                } else {
                    $this->error('修改失败！');
                }
                return;
            }
            $this->assign('list', $list);
            return $this->fetch('revise-com');
        }
        //\think\Log::record('未授权的管理员登陆');
        return $this->error('404 未知管理员');
    }

    public function detailsuser()
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $id = input('id');
            $this->assign('username', $user->getusername($userid));
            $user = new \app\api\controller\Users();
            $data = $user->getbasicuserbyid($id);
            if ($data) {
                $company = new \app\api\controller\Companies();
                $companydata = $company->getcompanydata($data['companyid']);
                $project = new \app\api\controller\Project();
                $projectdata = $project->getuserreadyproject($id);
                $this->assign("data", $data);
                $this->assign("id", $id);
                $this->assign('company', $companydata);
                $this->assign('project', $projectdata);
            } else {
                $this->error('未知用户信息');
            }
            return $this->fetch('details-user');
        }
        //\think\Log::record('未授权的管理员登陆');
        return $this->error('404 未知管理员');
    }
    public function detailscon($username)
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $this->assign('username', $user->getusername($userid));
            $user = \app\api\model\Contractors::get(['UserName' => $username]);
            $id = $user->ContractorID;
            if ($user) {
                $project = [];
                $list = ProjectModel::all(["Contractor" => $id]);
                foreach ($list as $value) {
                    $name = '';
                    $idlist = explode(',', $value->UserID);
                    foreach ($idlist as $value1) {
                        if ($value1) {
                            $UserID = \app\api\model\Users::get(['UserID' => $value1]);
                            $name = $name . $UserID->UserName . ' ';
                        }
                    }
                    $li = [
                        'ProjectName' => $value->ProjectName,
                        'Starttime' => $value->Starttime,
                        'SafetyGrade' => $value->SafetyGrade,
                        'username' => $name,
                        'State' => $value->State,
                    ];
                    array_push($project, $li);
                }
                $this->assign('project', $project);
                $this->assign('user', $user);
            } else {
                $this->error('未知用户信息');
            }
            return $this->fetch('details-con');
        }
        //\think\Log::record('未授权的管理员登陆');
        return $this->error('404 未知管理员');
    }

    public function detailscom()
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $this->assign('username', $user->getusername($userid));
            $id = input('id');
            $company = new \app\api\controller\Companies();
            $data = $company->getcompanydata($id);
            if ($data) {
                $this->assign('data', $data);
                $user = new \app\api\controller\Users();
                $userlist = $user->getuserlistbycompany($id);
                $this->assign('userlist', $userlist);
                return $this->fetch('details-com');
            } else {
                return $this->error("404 未知公司");
            }
        }
        //\think\Log::record('未授权的管理员登陆');
        return $this->error('404 未知管理员');
    }

    public function contractors()
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $this->assign('username', $user->getusername($userid));
            $list = \app\api\model\Contractors::all();
            $this->assign('list', $list);
            return $this->fetch('contractors');
        }
        //\think\Log::record('未授权的管理员登陆');
        return $this->error('404 未知管理员');
    }

    public function companies()
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $this->assign('username', $user->getusername($userid));
            $company = new \app\api\controller\Companies();
            $list = $company->getallcompanieslist();
            $this->assign('list', $list);
            return $this->fetch('companies');
        }
        //\think\Log::record('未授权的管理员登陆');
        return $this->error('404 未知管理员');
    }

    public function addcon()
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $this->assign('username', $user->getusername($userid));
            if (request()->isPost()) {
                // dump($_POST); die;
                $data = [
                    'ContractorID' => null,
                    'UserName' => input('UserName'),
                    'Gender' => input('Gender'),
                    'Birthday' => input('Birthday'),
                    'Idcard' => input('Idcard'),
                    'Nation' => input('Nation'),
                    'PhoneNumber' => input('PhoneNumber'),
                    'EmergencyContactName' => input('EmergencyContactName'),
                    'EmergencyContactPhone' => input('EmergencyContactPhone'),
                    'Jointime' => date('Y-m-d H:i'),
                    'WorkTime' => 0,
                    'SecurityPermissions' => input('SecurityPermissions'),
                    'Logintime' => '',
                ];
                if ($_FILES['FaceInformation']['tmp_name']) {
                    // 获取表单上传文件 例如上传了001.jpg
                    $file = request()->file('FaceInformation');
                    // 移动到框架应用根目录/public/uploads/ 目录下
                    // echo ROOT_PATH ;  die;
                    $info = $file->move(ROOT_PATH . 'public' . DS . '/static/faceinform', date('Ymd') . $this->makekeys() / date('Ymd') . $this->makekeys());
                    // var_dump($info); die;
                    if ($info) {
                        // 成功上传后 获取上传信息
                        $data['FaceInformation'] = date('Ymd') . $this->makekeys();
                        // 输出 42a79759f284b767dfcb2a0197904287.jpg
                        // echo $info->getFilename();  die;
                    } else {
                        // 上传失败获取错误信息
                        return $this->error($file->getError());
                    }
                }
                if (db('contractor')->insert($data)) {
                    return $this->success('添加成功！', 'admin/contractors');
                } else {
                    return $this->error('添加失败！');
                }
                return;
            }
            return $this->fetch('add-con');
        }
        //\think\Log::record('未授权的管理员登陆');
        return $this->error('404 未知管理员');
    }

    public function adduser()
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $this->assign('username', $user->getusername($userid));
            $company = new \app\api\controller\Companies();
            $list = $company->getallcompanieslist();
            $this->assign('list', $list);
            if (request()->isPost()) {
                $advantagelist = explode(" ", input('Advantage'));
                $data = [
                    'UserID' => null,
                    'UserName' => input('UserName'),
                    'Gender' => input('Gender'),
                    'Birthday' => input('Birthday'),
                    'Idcard' => input('Idcard'),
                    'Nation' => input('Nation'),
                    'Advantage' => json_encode($advantagelist),
                    'PhoneNumber' => input('PhoneNumber'),
                    'EmergencyContactName' => input('EmergencyContactName'),
                    'EmergencyContactPhone' => input('EmergencyContactPhone'),
                    'FaceInformation' => input('FaceInformation'),
                    'Jointime' => date('Y-m-d H:i'),
                    'WorkTime' => 0,
                    'Logintime' => '',
                    'SecurityPermissions' => input('SecurityPermissions'),
                ];
                if ($_FILES['FaceInformation']['tmp_name']) {
                    // 获取表单上传文件 例如上传了001.jpg
                    $file = request()->file('FaceInformation');
                    // 移动到框架应用根目录/public/uploads/ 目录下
                    // echo ROOT_PATH ;  die;
                    $info = $file->move(ROOT_PATH . 'public' . DS . '/static/faceinform');
                    // var_dump($info); die;
                    if ($info) {
                        // 成功上传后 获取上传信息
                        $data['FaceInformation'] = date('Ymd') . '/' . $info->getFilename();
                        // 输出 42a79759f284b767dfcb2a0197904287.jpg
                        // echo $info->getFilename();  die;
                    } else {
                        // 上传失败获取错误信息
                        return $this->error($file->getError());
                    }
                }
                if (db('user')->insert($data)) {
                    return $this->success('添加成功！', 'admin/unuser');
                } else {
                    return $this->error('添加失败！');
                }
                return;
            }
            return $this->fetch('add-user');
        }
        //\think\Log::record('未授权的管理员登陆');
        return $this->error('404 未知管理员');
    }

    public function addcom()
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $this->assign('username', $user->getusername($userid));
            if (request()->isPost()) {
                $advantagelist = explode(" ", input('Advantage'));
                $data = [
                    'ID' => null,
                    'Name' => input('CompanyName'),
                    'MembersNumber' => input('MembersNumber'),
                    'Members' => '',
                    'Advantage' => json_encode($advantagelist),
                    'BankCardNumber' => input('BankCardNumber'),
                    'Document' => input('Document'),
                    'PhoneNumber' => input('PhoneNumber'),
                    'SecurityPermissions' => input('SecurityPermissions'),
                    'CreditRating' => input('CreditRating'),
                    'PrincipalName' => input('PrincipalName'),
                    'JoinTime' => date('Y-m-d H:i'),
                ];
                if (db('companies')->insert($data)) {
                    return $this->success('添加成功！', 'admin/companies');
                } else {
                    return $this->error('添加失败！');
                }
                return;
            };
            return $this->fetch('add-com');
        }
        //\think\Log::record('未授权的管理员登陆');
        return $this->error('404 未知管理员');
    }
    public function user()
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $this->assign('username', $user->getusername($userid));
            $list = \app\api\model\Users::where('State', '=', '1')->order('UserID desc')->paginate();
            $this->assign('list', $list);
            return $this->fetch('user');
        }
        //\think\Log::record('未授权的管理员登陆');
        return $this->error('404 未知管理员');
    }

    public function unuser()
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $this->assign('username', $user->getusername($userid));
            $list = \app\api\model\Users::where('State', '=', '0')->order('UserID desc')->paginate();
            $this->assign('list', $list);
            return $this->fetch('unuser');
        }
        //\think\Log::record('未授权的管理员登陆');
        return $this->error('404 未知管理员');
    }

    public function kaoqing()
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $this->assign('username', $user->getusername($userid));
            $list = \app\api\model\Users::all();
            $this->assign('list', $list);
            return $this->fetch('kaoqing');
        }
        //\think\Log::record('未授权的管理员登陆');
        return $this->error('404 未知管理员');
    }

    public function kaoqinguser($id)
    {
        $user = new \app\api\controller\Administractors();
        $userid = $user->checkuser($_COOKIE['adminid']);
        if ($userid) {
            $this->assign('username', $user->getusername($userid));
            $log = new \app\api\controller\Log();
            $list = $log->getuserlog($id);
            $this->assign('list', $list);
            return $this->fetch('kaoqinguser');
        }
        //\think\Log::record('未授权的管理员登陆');
        return $this->error('404 未知管理员');
    }

    public function delunuser()
    {
        $id = input('id');
        if (db('user')->delete(input('id'))) {
            $this->success('删除成功！', 'admin/unuser');
        } else {
            $this->error('删除失败！');
        }

    }

    public function deluser()
    {
        $id = input('id');
        if (db('user')->delete(input('id'))) {
            $this->success('删除成功！', 'admin/user');
        } else {
            $this->error('删除失败！');
        }

    }

    public function examine()
    {
        $id = input('id');
        $list = db('user')->find($id);
        $data = [
            'UserID' => input('id'),
            'State' => 1,
        ];
        if (db('user')->update($data)) {
            $this->success('审核成功！', 'unuser');
        } else {
            $this->error('审核失败！');
        }
    }
    public function getstatus()
    {
        $server = new \app\api\controller\Server();
        return $server->getstatus();
    }
    public function getlog()
    {
        \think\Log::record('读取数据');
        return json(\think\Log::getLog());
    }

    public function makekeys($length = 12)
    {

        // 密码字符集，可任意添加你需要的字符
        $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's',
            't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D',
            'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

        // 在 $chars 中随机取 $length 个数组元素键名

        $keys = '';
        for ($i = 0; $i < $length; $i++) {
            // 将 $length 个数组元素连接成字符串
            $keys .= $chars[mt_rand(0, 61)];
        }
        return $keys;
    }
}
