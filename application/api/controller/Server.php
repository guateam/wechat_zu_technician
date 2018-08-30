<?php 
    namespace app\api\controller;
    use think\Controller;
    use app\api\model\Users;
    use app\api\model\Administractors;
    use app\api\model\Contractors;
    /**
     * server数据采集类
     * 可能弃用
     * 
     */
    class Server extends Controller{
        /**
         * 获取服务器的基本信息
         * 已实装
         * 可能弃用
         */
        public function getstatus(){
            $user=Users::where('OnlineStatus','>',0)->count();
            $administractor=Administractors::where('OnlineStatus','>',0)->count();
            $contractor=Contractors::where('OnlineStatus','>',0)->count();
            $data=['user'=>$user,'administractor'=>$administractor,'contractor'=>$contractor];
            return json($data); 
        } 
    }    
?>