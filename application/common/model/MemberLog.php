<?php

namespace app\common\model;

use think\Model;
/**
 * 用户会员升级记录表
 */
class MemberLog extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    // 追加属性
    protected $append = [];

    public static function upgrade($user_id,$old_id,$new_id,$mold,$money,$amount){
        $data = [
            'user_id' => $user_id,
            'old_id' => $old_id,
            'new_id' => $new_id,
            'mold' => $mold,
            'money' => $money,
            'amount' => $amount,
            'dates' => date('Y-m-d H:i:s'),
            'createtime' => time()
        ];
        self::insert($data);
    }
}
