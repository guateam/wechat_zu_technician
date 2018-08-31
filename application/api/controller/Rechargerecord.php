<?php
namespace app\api\controller;

use think\Controller;
use \app\api\model\Rechargerecord as Record;

class Rechargerecord extends Controller{
    public function get($job_number){
        $data = Record::all(['job_number'=>$job_number]);
        $result = [];
        if($data){
            foreach($data as $dt){
                array_push($result,[
                    'charge'=>$dt->charge,
                ]);
            }
        }
        return $result;
    }
}