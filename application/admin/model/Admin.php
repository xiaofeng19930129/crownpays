<?php

namespace app\admin\model;

use think\Model;
use think\Db;
class Admin extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $hidden = [
        'password',
        'salt'
    ];

    public static function init()
    {
        self::beforeWrite(function ($row) {
            $changed = $row->getChangedData();
            //如果修改了用户或或密码则需要重新登录
            if (isset($changed['username']) || isset($changed['password']) || isset($changed['salt'])) {
                $row->token = '';
            }
        });
    }

    public function adminMoney($admin_id){
        $money = $this->where('id',$admin_id)->value('money');
        return $money;
    }

    /**
     * 变更会员余额
     * @param int    $money   余额
     * @param int    $user_id 会员ID
     * @param string $memo    备注
     */
    public static function naira($admin_id,$money, $type, $source,$super_admin_id=0,$memo='')
    {
        $result = false;
        Db::startTrans();
        try {
            $admin = self::lock(true)->find($admin_id);
            if ($admin && $money != 0) {
                $before = $admin->naira;

                if($type == 1){
                    //增加余额
                    $after = bcadd($admin->naira,$money);
                }else{
                    // 减少余额
                    $after = bcsub($admin->naira,$money);
                }

                //更新会员信息
                $result = $admin->save(['naira' => $after]);
                //写入日志
                AdminMoneyLog::create([
                    'admin_id' => $admin_id,
                    'money' => $money, 
                    'before' => $before, 
                    'after' => $after, 
                    'source' => $source,
                    'super_admin_id' => $super_admin_id,
                    'memo' => $memo
                ]);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }

        return $result;
    }
}
