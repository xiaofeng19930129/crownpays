<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use fast\Random;
use think\Config;
use think\Validate;
use app\common\model\UserCash;
/**
 * 机器人接口
 */
class Robot extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    // public function _initialize()
    // {
    //     parent::_initialize();

    //     if (!Config::get('fastadmin.usercenter')) {
    //         $this->error(__('User center already closed'));
    //     }

    // }

    /**
     * 储蓄宝储蓄
     */
    public function receive(){

        // 消息内容，群名，群id，个人微信id，附带参数后续可扩展.....
        // $content = $this->request->post('content','');
        // $groupname = $this->request->post('groupname','');
        // $groupid = $this->request->post('groupid','');
        // $wechatid = $this->request->post('wechatid','');
        $params = $this->request->post();


        $validate = new \app\common\validate\Robot();
        if(!$validate->scene('receive')->check($params)){
            $this->error($validate->getError(),[],201);
        }
        $this->success('请求成功,接口开发中');

        $model = new \app\common\model\User();
        $result = $model->saveDeposit($this->auth->id,$params);
        
        if(!$result){
            $this->error(__('error'),[],$model->getErrorCode());
        }
        $this->success(__('success'));
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
            $this->error(__('error'),[],$model->getErrorCode());
        }
        $this->success(__('success'));
    }

    public function withdrawallist(){
        $model = new UserCash();
        $list = $model->getWithdrawalList($this->auth->id);
        $this->success(__('Success'),$list);
    }

}
