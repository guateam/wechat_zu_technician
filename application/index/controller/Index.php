<?php
namespace app\index\controller;
use think\Controller;

class Index extends Controller{
    public function index($id="AKB48"){
        $order=new \app\api\controller\Serviceorder();
        $data=$order->getdetaillistfortechnician($id);
        //本日时间
        $beginToday=date("Y-m-d");
        $endToday = $beginToday;
        $beginToday.=" 00:00:00";
        $endToday.=" 23:59:59";

        $beginMonth = date('Y-m-01', strtotime(date("Y-m-d")));
        $endMonth = date('Y-m-d', strtotime("$beginMonth +1 month -1 day"));
        $beginMonth.=" 00:00:00";
        $endMonth.=" 23:59:59";


        $today_sum=0;
        $today_salarysum=0;
        $today_count = 0;
        $month_sum=0;
        $month_salarysum=0;
        $month_count = 0;

        $bonus = "";
        $date = "";
        foreach($data as $value){
            $bonus.=$value['salary'].",";
            $date.=substr($value['date'],5,5).",";
            if($value['date']>=$beginToday && $value['date']<=$endToday)
            {
                $today_sum+=$value['price'];
                $today_salarysum+=$value['salary'];
                $today_count++;
            }
            if($value['date']>=$beginMonth && $value['date']<=$endMonth)
            {
                $month_sum+=$value['price'];
                $month_salarysum+=$value['salary'];
                $month_count++;
            }
        }
        $this->assign("today_sum",$today_sum);
        $this->assign("today_salarysum",$today_salarysum);
        $this->assign("month_sum",$month_sum);
        $this->assign("month_salarysum",$month_salarysum);
        $this->assign("today_count",$today_count);
        $this->assign("month_count",$month_count);
        $this->assign("bonus",$bonus);
        $this->assign("date",$date);
        return $this->fetch('index');
    }
}
