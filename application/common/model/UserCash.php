<?php

namespace app\common\model;

use think\Model;
use think\Db;
use Exception;
use think\exception\PDOException;
use think\exception\ValidateException;
/**
 * 用户提现记录
 */
class UserCash extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    // 追加属性
    protected $append = [];


    public function getWithdrawalList($user_id){
        return $this->where('user_id',$user_id)->order('id desc')->paginate(10);
    }

    /**
     * 提现
     */
    public function withdrawal($user_id,$params){
        $user = User::get($user_id);
        if($user['usdt'] < $params['amount']){
            //账户余额不足
            // $this->error = 'Insufficient account balance';
            $this->errorCode = 201;
            return false;
        }

        $saveData = [
            'user_id' => $user_id,
            'usdt' => $params['amount'],
            'wallet_address' => $params['wallet_address'],
            'createtime' => time()
        ];

        $result = false;
        Db::startTrans();
        try {
            //提现扣款
            User::usdt($user_id,$params['amount'],2,7,0);
            //操作成功后判断是升级还是降级
            $result = $this->save($saveData);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
        }
        return $result;
        return false;
    }

    public function getCashList($user_id){    
        $list = $this->where('user_id',$user_id)->order('id desc')->paginate(10);
        return $list;
    }
}
