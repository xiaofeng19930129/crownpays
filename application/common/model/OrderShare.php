<?php

namespace app\common\model;

use think\Model;
/**
 * 用户会员升级记录表
 */
class OrderShare extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    // 追加属性
    protected $append = [];

    public function getOrderShareList($user_id){
        $list = $this->where('parent_id',$user_id)->order('id desc')
            ->paginate(10)->each(function($item,$key){
                $item['user_name'] = User::where('id',$item['user_id'])->value('username');
                return $item;
            });
        
        return $list;
    }
}
