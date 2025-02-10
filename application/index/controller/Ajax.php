<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\UserCash;
use think\Lang;
use think\Response;

/**
 * Ajax异步请求接口
 * @internal
 */
class Ajax extends Frontend
{

    protected $noNeedLogin = ['lang', 'upload'];
    protected $noNeedRight = ['*'];
    protected $layout = '';

    /**
     * 加载语言包
     */
    public function lang()
    {
        $this->request->get(['callback' => 'define']);
        $header = ['Content-Type' => 'application/javascript'];
        if (!config('app_debug')) {
            $offset = 30 * 60 * 60 * 24; // 缓存一个月
            $header['Cache-Control'] = 'public';
            $header['Pragma'] = 'cache';
            $header['Expires'] = gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
        }

        $controllername = input("controllername");
        $this->loadlang($controllername);
        //强制输出JSON Object
        return jsonp(Lang::get(), 200, $header, ['json_encode_param' => JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE]);
    }

    /**
     * 生成后缀图标
     */
    public function icon()
    {
        $suffix = $this->request->request("suffix");
        $suffix = $suffix ? $suffix : "FILE";
        $data = build_suffix_image($suffix);
        $header = ['Content-Type' => 'image/svg+xml'];
        $offset = 30 * 60 * 60 * 24; // 缓存一个月
        $header['Cache-Control'] = 'public';
        $header['Pragma'] = 'cache';
        $header['Expires'] = gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
        $response = Response::create($data, '', 200, $header);
        return $response;
    }

    /**
     * 上传文件
     */
    public function upload()
    {
        return action('api/common/upload');
    }

    /**
     * 创建订单
     */
    public function create()
    {
        $params = $this->request->post();
        $validate = new \app\common\validate\Order();
        if(!$validate->scene('add')->check($params)){
            $this->error(__($validate->getError()));
        }
        $model = new \app\common\model\Order();
        $res = $model->createOrder($this->auth->id,$params);
        if(!$res){
            $this->ajaxError(__($model->getError() ?: 'Operation failed'));
        }
        $this->ajaxSuccess(__('Operation successful').','.__('Your order is ranked %s th',[$res]));
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
            $this->ajaxError(__($model->getError() ?: 'Operation failed'));
        }
        $this->ajaxSuccess(__('Operation successful'));
    }
    public function revoke(){
        $order_id = $this->request->request('order_id');
        $model = new \app\common\model\Order();
        $res = $model->orderRevoke($this->auth->id,$order_id);
        if(!$res){
            $this->ajaxError(__($model->getError() ?: 'Operation failed'));
        }
        $this->ajaxSuccess(__('Operation successful'));
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
        $this->ajaxSuccess(__('Operation successful'),$list);
    }
    public function deposit(){
        $params = $this->request->post();
        $validate = new \app\common\validate\User();
        if(!$validate->scene('deposit')->check($params)){
            $this->error(__($validate->getError()));
        }
        $model = new \app\common\model\User();
        $result = $model->saveDeposit($this->auth->id,$params);
        
        if(!$result){
            $this->ajaxError(__($model->getError() ?: 'Operation failed'));
        }
        $this->ajaxSuccess(__('Operation successful'));
    }

    public function withdrawal(){
        $params = $this->request->post();
        $validate = new \app\common\validate\UserCash();
        if(!$validate->scene('withdrawal')->check($params)){
            $this->error(__($validate->getError()));
        }
        $model = new UserCash();
        $result = $model->withdrawal($this->auth->id,$params);
        
        if(!$result){
            $this->ajaxError(__($model->getError() ?: 'Operation failed'));
        }
        $this->ajaxSuccess(__('Operation successful'));
    }

}
