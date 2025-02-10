<?php

namespace app\common\model;

use think\Model;
use traits\model\SoftDelete;
use Exception;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
class Order extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'order';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [];
    
    public $errorMsg = '';


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

    /**
     * 订单撤回
     */
    public function orderRevoke($user_id,$order_id){
        $order = $this->where('user_id',$user_id)->where('id',$order_id)->find();
        if(!$order){
            //订单不存在
            $this->error = 'Order does not exist';
            return false;
        }
        if($order['status'] != 1){
            //订单不存在
            $this->error = 'Order status error';
            return false;
        }

        $res = $this->where('id',$order_id)->update([
            'status' => 2,//已撤单
            'revoketime' => time(),//撤回时间
        ]);
        
        return $res;
    }


    /**
     * 订单申诉
     */
    public function orderAppeal($user_id,$params){
        $order = $this->where('user_id',$user_id)->where('id',$params['order_id'])->find();
        if(!$order){
            //订单不存在
            $this->error = 'Order does not exist';
            return false;
        }
        


        //查询当前订单未回复的申诉内容
        $appealModel = new Appeal();
        $appeal = $appealModel->where('order_id',$order['id'])->where('status',1)->find();
        if($appeal){
            //当前订单已申诉，请耐心等待回复
            $this->error = 'The current order has been appealed, please be patient and wait for a response';
            return false;
        }
        if(!in_array($order['status'],[4,6])){
            //订单状态错误
            $this->error = 'Order status error';
            return false;
        }
        
        $appealData = [
            'order_id' => $order['id'],
            'user_id' => $order['user_id'],
            'content'  => $params['content'],
            'status'   => 1,
            'createtime' => time(),
            'updatetime' => time(),
        ];
        $res = $appealModel->save($appealData);
        if($res){
            $this->where('id',$order['id'])->update([
                'appealtime' => time(),//申诉时间
                'status'     => 5,//申诉中
            ]);
        }
        return $res;
    }

    public function createOrder($user_id,$params){

        $user = User::get($user_id);

        //汇率
        $userMember = (new Member)->getUserMember($user['member_id']);
        $config = Config::getConfigArray([
            'expense_one_fixed',
            'expense_one_permillage',
            'expense_two',
            'bank_charges'
        ]);

        $totalMaria = 0;
        $totalUsdt = 0;
        $totalExpense = 0;//
        $expense_fixed = 0;
        $expense_ratio = 0;
        $expense_permillage = 0;
        if($params['expense_type'] == 1){
            $expense_fixed = $config['expense_one_fixed'];
            $expense_permillage = $config['expense_one_permillage'];
            $ratio = bcdiv($expense_permillage,1000,3);

            //固定手续费 + 百分比
            $totalExpense = bcadd($totalExpense,$expense_fixed,3);
            
            //比例手续费
            $expense_ratio = bcmul($params['naira'],$ratio,3);
            $totalExpense = bcadd($totalExpense,$expense_ratio,3);

        }else{
            //固定手续费
            $totalExpense = $config['expense_two'];
            $expense_fixed = $config['expense_two'];
        }

        $totalMaria = bcadd($params['naira'],$totalExpense,3);

        $totalUsdt = bcdiv($totalMaria,$userMember['NGNUSD'],3);

        
        //判断手续费是否充足
        if(!User::checkMoney($user_id,$totalUsdt)){
            // $this->error = 'Insufficient account USDT';
            $this->errorCode = 202;
            return false;
        }

        $result = false;
        Db::startTrans();
        try {
            $order = [
                'user_id' => $user_id,
                'sn' => $this->order_sn(),
                // 'content' => $params['content'],
                'cardnumber' => $params['cardnumber'],
                'bankname' => $params['bankname'],
                'naira' => $params['naira'],
                'exchange' => $userMember['NGNUSD'],
                'expense_type'  => $params['expense_type'],
                'expense_fixed'  => $expense_fixed,
                'expense_ratio'  => $expense_ratio,
                'expense_permillage' => $expense_permillage,
                'total_expense' => $totalExpense,
                'bank_charges' => $config['bank_charges'],
                'usdt' =>  $totalUsdt,
                'total_naira' => $totalMaria,
                'status' => 1,
                'createtime' => time(),
                'updatetime' => time()
            ];
            $result = $this->save($order);
            if($result){
                // $memo = "奈拉数量：{$params['naira']},手续费：{$totalExpense},USDT:{$totalUsdt}";
                //扣款
                User::usdt($user_id,$totalUsdt,2,3,0,$this->id);
                //前面还有多少未接单
                // $arrange = $this->where('id','<=',$this->id)->where('status',1)->count();
                (new User)->current($user['id'],$this->id);
            }
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
        }
        if (false === $result) {
            return false;
        }
        return true;
    }

    /**
     * 生成订单编号
     */
    public function order_sn(){
        return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

}
