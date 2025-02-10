<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use fast\Random;
use think\Config;
use think\Validate;
use app\common\model\User;
use app\common\model\Member;
/**
 * USDT订单接口
 */
class Uorder extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();

        $this->model = new \app\common\model\Uorder();

    }
    /**
    * 创建订单
    */
    public function create()
    {
       $params = $this->request->post();
       $validate = new \app\common\validate\Uorder();
       if(!$validate->scene('add')->check($params)){
           $this->error(__('error'),[],201);
       }
       $model = new \app\common\model\Uorder();
       $res = $model->createOrder($this->auth->id,$params);
       if($res === false){
           $this->error(__($model->getError() ?: 'error'),[],$model->getErrorCode());
       }
       $this->success(__('success'),$res);
    }

    /**
     * 订单列表
     */
    public function orderlist(){
        $time = $this->request->get('time');
        $model = new \app\common\model\Uorder();
        $list = $model->getOrderList($this->auth->id,$time);
        $this->success(__('Success'),$list);
    }


    

   /**
    * 订单回调
    */
   public function callback()
   {
       $params = $this->request->post();

       
    //    $validate = new \app\common\validate\Order();
    //    if(!$validate->scene('appeal')->check($params)){
    //        $this->error(__($validate->getError()));
    //    }

       $model = new \app\common\model\Uorder();
       $res = $model->orderCallback($params);
       if(!$res){
           $this->error(__($model->getError() ?: 'Operation failed'));
       }
       $this->success(__('Operation successful'));
   }
}
