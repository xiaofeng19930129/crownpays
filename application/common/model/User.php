<?php

namespace app\common\model;

use think\Db;
use think\Model;
use Exception;
use think\exception\PDOException;
use think\exception\ValidateException;
/**
 * 会员模型
 */
class User extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'url',
        'order_num',
    ];

    public function security($user_id){
        $user = $this->get($user_id);

        $amount = 200;
        if($user){

            if($user['security'] == 1){
                $this->errorCode = 201;
                return false;
            }

            if($user['usdt'] < $amount){
                //账户余额不足
                $this->errorCode = 202;
                return false;
            }
            $result = $user->save(['security' => 1]);
            if($result){
                User::usdt($user_id,$amount,2,9,0);
                return true;
            }
        }
        return false;
    }

    public function setSubAccountExchangeDiff($fid,$user_id,$exchange_diff){
        $user = $this->get($user_id);
        if(!$user || $user['fid'] != $fid){
            $this->errorCode = 202;
        }
        $result = $user->where('id',$user_id)->update(['exchange_diff'=>$exchange_diff]);
        return $result;
    }

    public function getSubAccountList($fid){
        $list = $this->where('fid',$fid)->field('id,username,nickname,pid,avatar,exchange_diff')->order('id desc')->paginate(10);
        return $list;
    }


    /**
     * 代理统计
     */
    public function agentCensus($user_id){
        //邀请人数
        $childrenIds = $this->where('pid',$user_id)->column('id');
        // 下单数量
        $orderCount = Order::where('id','in',$childrenIds)->count();
        // 返佣金额
        $orderShareMoney = OrderShare::where('parent_id',$user_id)->sum('rake_usdt');

        return [
            'userCount' => count($childrenIds),
            'orderCount' => $orderCount,
            'orderShareMoney' => $orderShareMoney
        ];
    }


    /**
     * 储蓄宝操作
     */
    public function saveDeposit($user_id,$params){
        $user = $this->get($user_id);
        if($user){

            if($params['type'] == 1){
                //转入
                if($user['usdt'] < $params['amount']){
                    //账户余额不足
                    $this->errorCode = 202;
                    return false;
                }
            }else{
                // 转出
                if($user['deposit'] < $params['amount']){
                    //储蓄宝余额不足
                    $this->errorCode = 203;
                    return false;
                }
            }
            $result = false;
            Db::startTrans();
            try {
                if($params['type'] == 1){
                    //转入
                    User::usdt($user_id,$params['amount'],2,5,0);
                    User::deposit($user_id,$params['amount'],1,1,0);
                }else{
                    //转出
                    User::usdt($user_id,$params['amount'],1,6,0);
                    User::deposit($user_id,$params['amount'],2,2,0);
                }
                //操作成功后判断是升级还是降级
                $this->membership($user_id);
                $result = true;
                Db::commit();
            } catch (ValidateException|PDOException|Exception $e) {
                Db::rollback();
            }
            return $result;
        }
        return false;
    }


    public function current($user_id,$order_id){

        $user = $this->get($user_id);
        $order = Order::get($order_id);
        //给自己增加流水
        User::where('id',$user['id'])->update([
            'current_naira' => bcadd($user['current_naira'],$order['naira']),
            'total_current_naira' => bcadd($user['total_current_naira'],$order['naira']),
        ]);

        $parent = false;
        if($user['pid']){
            $parent = $this->get($user['pid']);
            //增加一半流水
            $halfNaira = bcdiv($order['naira'],2,3);
            User::where('id',$parent['id'])->update([
                'current_naira' => bcadd($parent['current_naira'],$halfNaira,3),
                'total_current_naira' => bcadd($parent['total_current_naira'],$halfNaira,3),
            ]);

            //是官方代理
            if($parent['agent'] == 1){

                // 官方代理,需要给上级用户汇率差
                if($user['member_id'] < $parent['member_id']){
                    //当前用户汇率
                    $userMemberExchange = Member::where('id',$user['member_id'])->value('NGNUSD');
                    $parentMemberExchange = Member::where('id',$parent['member_id'])->value('NGNUSD');

                    $userUsdt = bcdiv($order['naira'],$userMemberExchange,3);
                    $parentUsdt = bcdiv($order['naira'],$parentMemberExchange,3);

                    //代理赚的汇率差
                    $parentAgentUstd = bcsub($parentUsdt,$userUsdt,3);
                    // $memo = "用户汇率:{$userMemberExchange},用户U:{$userUsdt},父级汇率:{$parentMemberExchange},父级U:{$parentUsdt},u差价:{$parentAgentUstd}";
                    self::usdt($parent['id'],$parentAgentUstd,1,4,0,$order_id);

                    // 代理分佣记录
                    OrderShare::create([
                        'user_id'=>$user['id'],
                        'parent_id' => $parent['id'],
                        'order_id' => $order['id'],
                        'user_member_id' => $user['member_id'],
                        'user_member_exchange'=> $userMemberExchange,
                        'user_member_usdt' => $userUsdt,
                        'parent_member_id' => $parent['member_id'],
                        'parent_member_exchange'=>$parentMemberExchange,
                        'parent_member_usdt' => $parentUsdt,
                        'order_naira' => $order['naira'],
                        'rake_usdt' => $parentAgentUstd,
                        'dates' => date('Y-m-d H:i:s'),
                        'createtime'=>time()
                    ]);

                }
            }
            $this->membership($parent['id']);
        }
        $this->membership($user['id']);
    }

    
    /**
     * 判断会员是否升级
     */
    public function membership($user_id){
        $user = $this->get($user_id);

        // $user['current_naira'] = 1000000000;
        // $user['deposit'] = 6000;

        //查询会员列表,可以达到的条件
        $member = Member::where('naira_stream','<=',$user['current_naira'])->whereOr('usdt_balance','<=',$user['deposit'])->order('id desc')->find();
        // dd($member);
        if($user['member_id'] != $member['id']){

            $this->where('id',$user['id'])->update(['member_id'=>$member['id']]);
            if($member['naira_stream'] <= $user['current_naira']){
                MemberLog::upgrade($user['id'],$user['member_id'],$member['id'],1,$user['current_naira'],$member['naira_stream']);
            }else{
                MemberLog::upgrade($user['id'],$user['member_id'],$member['id'],2,$user['deposit'],$member['usdt_balance']);
            }

        }

        // $memberNext = Member::where('id','>',$user['member_id'])->order('id asc')->find();
        // //流水超过下一个等级
        // if($user['current_naira'] >= $memberNext['naira_stream']){
        //     $this->where('id',$user['id'])->update(['member_id'=>$memberNext['id']]);
        //     MemberLog::upgrade($user['id'],$user['member_id'],$memberNext['id'],1,$user['current_naira'],$memberNext['naira_stream']);
        //     return true;
        // }
        // // 储蓄宝超过下一个等级
        // if($user['deposit'] >= $memberNext['usdt_balance']){
        //     $this->where('id',$user['id'])->update(['member_id'=>$memberNext['id']]);
        //     MemberLog::upgrade($user['id'],$user['member_id'],$memberNext['id'],2,$user['deposit'],$memberNext['usdt_balance']);
        //     return true;
        // }
    }

    /**
     * 获取个人URL
     * @param string $value
     * @param array  $data
     * @return string
     */
    public function getUrlAttr($value, $data)
    {
        return "/u/" . $data['id'];
    }
    /**
     * 获取订单数量
     * @param string $value
     * @param array  $data
     * @return string
     */
    public function getOrderNumAttr($value, $data)
    {
        return Order::where('user_id',$data['id'])->count();
    }
    /**
     * 获取邀请人
     * @param string $value
     * @param array  $data
     * @return string
     */
    public function getInviterAttr($value, $data)
    {
        return $this->where('id',$data['pid'])->value('username');
    }

    

    public function checkCode($code){
        $pid = $this->where('code',$code)->value('id');
        return $pid;
    }

    public function getCode(){
        $code = substr(base_convert(md5(uniqid(md5(microtime(true)),true)), 16, 10), 0, 6);
        if($this->where('code',$code)->find()){
            return $this->getCode();
        }
        return $code;
    }


    /**
     * 获取头像
     * @param string $value
     * @param array  $data
     * @return string
     */
    public function getAvatarAttr($value, $data)
    {
        if (!$value) {
            //如果不需要启用首字母头像，请使用
            //$value = '/assets/img/avatar.png';
            $value = letter_avatar($data['nickname']);
        }
        return $value;
    }

    /**
     * 获取会员的组别
     */
    public function getGroupAttr($value, $data)
    {
        return UserGroup::get($data['group_id']);
    }

    /**
     * 获取验证字段数组值
     * @param string $value
     * @param array  $data
     * @return  object
     */
    public function getVerificationAttr($value, $data)
    {
        $value = array_filter((array)json_decode($value, true));
        $value = array_merge(['email' => 0, 'mobile' => 0], $value);
        return (object)$value;
    }

    /**
     * 设置验证字段
     * @param mixed $value
     * @return string
     */
    public function setVerificationAttr($value)
    {
        $value = is_object($value) || is_array($value) ? json_encode($value) : $value;
        return $value;
    }

    /**
     * 变更会员余额
     * @param int    $money   余额
     * @param int    $user_id 会员ID
     * @param string $memo    备注
     */
    public static function usdt($user_id,$usdt, $type, $source,$admin_id=0,$memo='')
    {
        Db::startTrans();
        try {
            $user = self::lock(true)->find($user_id);
            if ($user && $usdt != 0) {
                $before = $user->usdt;

                if($type == 1){
                    //增加余额
                    $after = bcadd($user->usdt,$usdt,3);
                }else{
                    // 减少余额
                    $after = bcsub($user->usdt,$usdt,3);
                }

                //更新会员信息
                $user->save(['usdt' => $after]);
                //写入日志
                MoneyLog::create([
                    'user_id' => $user_id, 
                    'money' => $usdt, 
                    'before' => $before, 
                    'after' => $after, 
                    'source' => $source,
                    'purse' => 1,
                    'admin_id' => $admin_id,
                    'memo' => $memo
                ]);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }
    }
    /**
     * 变更会员余额
     * @param int    $money   余额
     * @param int    $user_id 会员ID
     * @param string $memo    备注
     */
    public static function deposit($user_id,$deposit, $type, $source,$admin_id,$memo='')
    {
        Db::startTrans();
        try {
            $user = self::lock(true)->find($user_id);
            if ($user && $deposit != 0) {
                $before = $user->deposit;

                if($type == 1){
                    //增加余额
                    $after = bcadd($user->deposit,$deposit,3);
                }else{
                    // 减少余额
                    $after = bcsub($user->deposit,$deposit,3);
                }

                //更新会员信息
                $user->save(['deposit' => $after]);
                //写入日志
                MoneyLog::create([
                    'user_id' => $user_id, 
                    'money' => $deposit, 
                    'before' => $before, 
                    'after' => $after, 
                    'source' => $source,
                    'purse' => 2,
                    'admin_id' => $admin_id,
                    'memo' => $memo
                ]);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }
    }
    /**
     * 变更会员积分
     * @param int    $score   积分
     * @param int    $user_id 会员ID
     * @param string $memo    备注
     */
    public static function score($score, $user_id, $memo)
    {
        Db::startTrans();
        try {
            $user = self::lock(true)->find($user_id);
            if ($user && $score != 0) {
                $before = $user->score;
                $after = $user->score + $score;
                $level = self::nextlevel($after);
                //更新会员信息
                $user->save(['score' => $after, 'level' => $level]);
                //写入日志
                ScoreLog::create(['user_id' => $user_id, 'score' => $score, 'before' => $before, 'after' => $after, 'memo' => $memo]);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }
    }

    /**
     * 根据积分获取等级
     * @param int $score 积分
     * @return int
     */
    public static function nextlevel($score = 0)
    {
        $lv = array(1 => 0, 2 => 30, 3 => 100, 4 => 500, 5 => 1000, 6 => 2000, 7 => 3000, 8 => 5000, 9 => 8000, 10 => 10000);
        $level = 1;
        foreach ($lv as $key => $value) {
            if ($score >= $value) {
                $level = $key;
            }
        }
        return $level;
    }

    // 判断余额是否充足
    public static function checkMoney($user_id,$usdt){
        $user = self::where('id',$user_id)->find();
        if($user['usdt'] >= $usdt){
            return true;
        }
        return false;
    }

}
