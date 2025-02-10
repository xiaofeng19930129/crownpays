<?php

namespace app\common\model;

use think\Model;
use traits\model\SoftDelete;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
class Uorder extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'uorder';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [];
    
    public $errorMsg = '';

    public function orderCallback($params){
        $order = $this->where('createtime','>',strtotime('today'))->where('amount',$params['amount'])->order('id desc')->find();
        $result = false;
        if($order['status'] != 1){
            $this->error = '订单状态错误';
            return false;
        }

        Db::startTrans();
        try {
            $saveData = [
                'to_address' => $params['to_address'],
                'from_address' => $params['from_address'],
                'type' => $params['type'],
                'token_name' => $params['tokenName'],
                'token_type' => $params['tokenType'],
                'final_result' => $params['final_result'],
                'resulttime' =>  time(),
                'status' => 3
            ];
            $result = $this->where('id',$order['id'])->update($saveData);
            //给用户usdt加钱
            User::usdt($order['user_id'],$order['amount'],1,10,0);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
        }
        return $result;
    }

    public function getOrderList($user_id,$time){

        $where = ['user_id'=>$user_id];

        if($time){
            $timeArray = explode(' - ',$time);
            $start = strtotime($timeArray[0]);
            $end = strtotime($timeArray[1]);
            $where['createtime'] = ['between time', [$start, $end]];
        }
        $list = $this->where($where)->order('id desc')->paginate(10);

        return $list;
    }

    public function createOrder($user_id,$params){

        $user = User::get($user_id);

        

        //查询今天是多少单子
        
        $result = false;
        Db::startTrans();
        try {

            $serial = $this->where('createtime','>=',strtotime('today'))->max('serial');
            $maxSerial = $serial + 1;
            $serialAmount = bcdiv($maxSerial,1000,3);
            $orderAmount = bcadd($params['amount'],$serialAmount,3);
            $sn = $this->order_sn();
            $order = [
                'serial' => $maxSerial,
                'user_id' => $user_id,
                'sn' => $sn,
                'amount' =>  $orderAmount,
                'status' => 1,
            ];
            
            $result = $this->save($order);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
        }
        if (false === $result) {
            return false;
        }
        return ['sn'=>$sn,'amount' => $orderAmount];
    }

    /**
     * 生成订单编号
     */
    public function order_sn(){
        return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

}
