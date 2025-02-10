<?php

namespace app\admin\model;

use fast\Tree;
use think\Model;

class AdminMoneyLog extends Model
{

    // 表名
    protected $name = 'admin_money_log';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    // 追加属性
    protected $append = [];

    public function getSourceList()
    {
        return [1 => __('Administrator recharge'), 2 => __('Administrator deduction'),3 => __('Deduction for accepting orders')];
    }
    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function admin()
    {
        return $this->belongsTo('Admin', 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
