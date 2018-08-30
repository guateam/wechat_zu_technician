<?php
namespace app\api\controller;
use think\Controller;
use app\api\model\Companies as CompanyModel;
/**
 * 公司数据库操作类
 * 部分实装
 */
class Companies extends Controller{
    /**
     * 获取公司的雷达数据
     * 已实装
     * 2018-3-2 张煜
     */
    public function getradardata($id){//获取公司雷达数据
        $company=CompanyModel::get(["ID"=>$id]);
        $user=new \app\api\controller\Users();
        $userlist=json_decode($company->Members);
        $count=0;
        $ontime=0;
        $price=0;
        $key=0;
        $list=[];
        foreach($userlist as $key=>$value){
            $item=$user->getradardata($value);
            $ontime+=$item[3];
            $price+=$item[5];
            foreach($item[7] as $value1){
                if(!\in_array($value1,$list)){
                    array_push($list,$value1);
                }
            }
        }
        $count=count($list);
        $key++;
        $workingtime=($company->TotalWorkTime/$company->MembersNumber);
        $languagecount=count(json_decode($company->Advantage,true));
        $data=[
            $count,
            $languagecount,
            $workingtime,
            $ontime/$key,
            $company->SecurityPermissions,
            $price/$key,
            $company->CreditRating
        ];
        return $data;
    }
    /**
     * 通过id获取公司名
     * 已实装
     */
    public function getcompanyname($id){
        $company=CompanyModel::get(["ID"=>$id]);
        return $company->Name;
    }
    /**
     * 获取公司详细信息
     * 已实装
     */
    public function getcompanydata($id){
        $company=CompanyModel::get(["ID"=>$id]);
        $advantage='';
        foreach(json_decode($company->Advantage) as $value){
            $advantage=$advantage.$value." ";
        }
        $data=[
            "name"=>$company->Name,
            "safetygrade"=>$company->SecurityPermissions,
            "creditrating"=>$company->CreditRating,
            "advantage"=>$advantage,
            "totalworktime"=>$company->TotalWorkTime,
            "jointime"=>$company->JoinTime,
            "membernum"=>$company->MembersNumber,
            "phonenumber"=>$company->PhoneNumber,
            "id"=>$company->ID,
            "document"=>$company->Document,
            "bankcardnumber"=>$company->BankCardNumber,
            "PrincipalName"=>$company->PrincipalName
        ];
        return $data;
    }
    /**
     * 新建公司
     * 已实装
     */
    public function add($name,$document,$bankcardnumber,$phonenumber,$principalname){//新建公司
        $company = new CompanyModel();
        $company->data([
            "Name"=>$name,
            "JoinTime"=>date('Y-m-d H:i'),
            "Document"=>$document,
            "BankCardNumber"=>$bankcardnumber,
            "PhoneNumber"=>$phonenumber,
            "PrincipalName"=>$principalname
        ]);
    }
    /**
     * 设置公司成员（覆盖）
     * 已实装
     */
    public function setmembers($id,$memberlist){//设置公司成员
        $company = CompanyModel::get(["ID"=>$id]);
        $company->Members=json($memberlist);
        $company->MembersNumber=count($memberlist);
        $company->save();
    }
    /**
     * 设置公司成员（不覆盖）
     */
    public function addmembers($id,$memberlist){//添加公司成员
        $company = CompanyModel::get(["ID"=>$id]);
        $list=json_decode($company->Members,true);
        foreach ($memberlist as $value) {
            array_push($list,$value);
        }
        $company->Members=json_encode($list);
        $company->MembersNumber=count($list);
        $company->save();
    }
    /**
     * 清除公司
     * 已实装
     */
    public function delete($id){//清除公司
        $company=companyModel::get(["ID"=>$id]);
        $company->delete();
    }
    /**
     * 获取公司列表
     * 2018-3-8 张煜
     */
    public function getallcompanieslist(){
        $list=companyModel::all();
        $data=[];
        foreach($list as $value){
            $advantage='';
            foreach(json_decode($value->Advantage) as $value1){
                $advantage=$advantage.$value1.' ';
            }
            $item=[
                'name'=>$value->Name,
                'advantage'=>$advantage,
                'membersnumber'=>$value->MembersNumber,
                'creditrating'=>$value->CreditRating,
                'safetygrade'=>$value->SecurityPermissions,
                'worktime'=>$value->TotalWorkTime,
                'id'=>$value->ID,
                "Document"=>$value->Document
            ];
            array_push($data,$item);
        }
        return $data;
    }
    /**
     * 通过公司名获取id
     * 2018-3-17 袁宜照
     */
    public function getcompanyid($name){
        if($name == '无')return json(['status'=>2]);
        $company=CompanyModel::get(["Name"=>$name]);
        if($company)
        {
            return json(['status'=>1,'id'=>$company->ID]);
        }
        else return json(['status'=>0]);
    }
    public function getcompanyidforuser($companyname){
        $company=CompanyModel::get(["Name"=>$companyname]);
        if($company){
            return $company->ID;
        }
    }
    public function settotalworktime($id){
        $company=CompanyModel::get(["ID"=>$id]);
        $total=0;
        if($company){
            $list=json_decode($company->Members);
            foreach($list as $value){
                $user=\app\api\model\Users::get(["UserID"=>$value]);
                if($user){
                    $total+=$user->WorkTime;
                }
            }
            $company->TotalWorkTime=$total/$company->MembersNumber;
            $company->save();
        }

    }
    public function setCreditRating($id){
        $company=CompanyModel::get(['ID'=>$id]);
        if($company){
            $list=json_decode($company->Members);
            $total=0;
            foreach($list as $value){
                $user=\app\api\model\Users::get(["UserID"=>$value]);
                if($user){
                    $total+=$user->CreditRating;
                }
            }
            $company->CreditRating=$total/((count($list)>0)?count($list):1);
        }
        
    }
}