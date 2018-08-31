<?php
namespace app\api\controller;

use think\Controller;
use \app\api\model\Servicetype as Service;

class Servicetype extends Controller
{

    public function get_type(){
        $service_type = Service::all();
        $vip = \app\api\model\Vipinformation::all();
        $max_discount = 100;
        foreach($vip as $lv){
            if($lv->discount_ratio < $max_discount) 
                $max_discount = $lv->discount_ratio;
        }
        $type = [];
        if(!$service_type){
           return json(['status'=>0,'data'=>[]]);
        }else{
            foreach($service_type as $tp){
                array_push($type,[
                    "name"=>$tp->name,
                    "info"=>$tp->info,
                    "duration"=>$tp->duration,
                    "price"=>$tp->price/100,
                    "discount"=>$tp->discount/100
                ]);
            }
            return ['status'=>1,'data'=>$type,'max_discount'=>$max_discount/100];
        }
    }


}