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
 * 订单接口
 */
class Order extends Api
{
    protected $noNeedLogin = ['exchange'];
    protected $noNeedRight = '*';
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();

        $this->model = new \app\common\model\Order();
        

    }

    /**
     * 获取用户汇率
     */
    public function exchange(){
        $member = (new Member)->getUserMember($this->auth->isLogin() ? $this->auth->member_id : 1);
        $this->success(__('success'),$member);
    }

    /**
    * 创建订单
    */
    public function create()
    {
       $params = $this->request->post();
       $validate = new \app\common\validate\Order();
       if(!$validate->scene('add')->check($params)){
           $this->error(__('error'),[],201);
       }
       $model = new \app\common\model\Order();
       $res = $model->createOrder($this->auth->id,$params);
       if($res !== true){
           $this->error(__($model->getError() ?: 'error'),[],$model->getErrorCode());
       }
       $this->success(__('success'));
    }

    /**
     * 订单列表
     */
    public function orderlist(){
        $time = $this->request->get('time');
        $model = new \app\common\model\Order();
        $list = $model->getOrderList($this->auth->id,$time);
        $this->success(__('Success'),$list);
    }


    

   /**
    * 订单申诉
    */
   public function appeal()
   {
       $params = $this->request->post();

       $validate = new \app\common\validate\Order();
       if(!$validate->scene('appeal')->check($params)){
           $this->error(__($validate->getError()));
       }

       $model = new \app\common\model\Order();
       $res = $model->orderAppeal($this->auth->id,$params);
       if(!$res){
           $this->error(__($model->getError() ?: 'Operation failed'));
       }
       $this->success(__('Operation successful'));
   }
   public function revoke(){
       $order_id = $this->request->request('order_id');
       $model = new \app\common\model\Order();
       $res = $model->orderRevoke($this->auth->id,$order_id);
       if(!$res){
           $this->error(__($model->getError() ?: 'Operation failed'));
       }
       $this->success(__('Operation successful'));
   }

   /**
    * 获取订单申诉记录
    */
   public function getOrderAppeallist(){
       $order_id = $this->request->request('order_id');
       $list = \app\common\model\Appeal::where('order_id',$order_id)->order('id desc')->select();
       foreach ($list as $key => $item) {
           if($item['status'] == 2) $item['replytime'] = date("Y-m-d H:i:s",$item['replytime']);
           $item['createtime'] = date("Y-m-d H:i:s",$item['createtime']);
       }
       $this->success(__('Operation successful'),$list);
   }
   

}
