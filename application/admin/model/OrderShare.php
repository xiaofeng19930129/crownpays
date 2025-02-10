<?php

namespace app\admin\model;

use think\Model;


class OrderShare extends Model
{
    // 表名
    protected $name = 'order_share';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [];

    public function order()
    {
        return $this->belongsTo('app\admin\model\Order', 'order_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'parent_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
