<?php

namespace app\common\model;

use think\Model;

class Appeal extends Model
{



    // 表名
    protected $name = 'appeal';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [];
    
    public function getStatusList()
    {
        return ['1' => __('Status 1'), '2' => __('Status 2')];
    }



    public function order()
    {
        return $this->belongsTo('Order', 'order_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
