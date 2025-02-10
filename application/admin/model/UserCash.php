<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class UserCash extends Model
{

    use SoftDelete;
    // 表名
    protected $name = 'user_cash';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [];
    
    public function getAuditList()
    {
        return ['1' => __('Audit 1'), '2' => __('Audit 2'), '3' => __('Audit 3')];
    }

    public function getAuditTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['audit']) ? $data['audit'] : '');
        $list = $this->getAuditList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getAuditTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['audit_time']) ? $data['audit_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setAuditTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function admin()
    {
        return $this->belongsTo('app\admin\model\Admin', 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
