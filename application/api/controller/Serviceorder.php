<?php
namespace app\api\controller;

use think\Controller;
use \app\api\model\Serviceorder as Service;

class Serviceorder extends Controller
{
    /**
     * 为技师获取订单详情
     * 2018-8-30 创建 张煜
     * @param mixed $id 技师工号
     */
    public function getdetaillistfortechnician($id)
    {
        $servicelist = Service::all(['job_number' => $id]);
        $data = [];
        foreach ($servicelist as $value) {
            $order = \app\api\model\Consumedorder::get(['order_id' => $value->order_id]);
            if ($order) {
                if ($order->state == 4) {
                    $servicetype = '其他';
                    switch ($value->service_type) {
                        case 1:
                            $servicetype = '店内服务';
                            break;
                        case 2:
                            $servicetype = '酒水饮料';
                            break;
                        case 3:
                            $servicetype = '外卖订单';
                            break;
                    }
                    $price = $value->price/100;
                    $servicename = '其他';
                    $salary = 0;
                    if ($value->price == '') {
                        if ($value->service_type == 1) {
                            $service = \app\api\model\Servicetype::get(['id' => $value->item_id]);
                            if ($service) {
                                $servicename = $service->name;
                                $price = $service->price/100;
                                $salary = $service->commission/100;
                            }
                        } else {
                            $service = \app\api\model\Itemtype::get(['id' => $value->item_id]);
                            if ($service) {
                                $servicename = $service->name;
                                $price = $service->price/100;
                                $salary = 0;
                            }
                        }
                    }
                    $roomid='未知';
                    //if($room){
                        $roomid=$value->private_room_number;
                    //}
                    $clocktype='未知';
                    switch($value->clock_type){
                        case 1:
                            $clocktype='排钟';
                            break;
                            case 2:
                            $clocktype='点钟';
                            break;
                    }
                    $item = [
                        'id' => $value->ID,
                        'orderid' => $value->order_id,
                        'price' => $price,
                        'servicetype' => $servicetype,
                        'roomid' => $roomid,
                        'name' => $servicename,
                        'serviceid' => $value->item_id,
                        'salary' => $salary,
                        'date' =>$order->generated_time,
                        'servicetypeid'=>$value->service_type,
                        'clocktype'=>$clocktype
                    ];
                    array_push($data,$item);
                }
            }
        }
        return $data;
    }
}
