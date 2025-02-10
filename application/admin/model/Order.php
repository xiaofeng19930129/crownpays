<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

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
    protected $append = [
        'skip_text',
        'accepttime_text',
        'revoketime_text',
        'finishtime_text',
        'appealtime_text',
        'status_text'
    ];
    
    public function orderPageStatistics($admin_id,$isSuperAdmin=false){
        $starttime = strtotime('today midnight');
        $endtime = strtotime('tomorrow midnight', $starttime);

        $where = [
            
            'accepttime' => ['between time',[$starttime, $endtime]],
            'status' => ['>',2],
        ];

        // if(!$isSuperAdmin){
        //     $where['admin_id'] = $admin_id;
        // }


        $total = $this->where($where)->count();
        $money = $this->where($where)->sum('usdt');
        return [
            'total' => $total,
            'money' => $money,
        ];
    }

    //接单验证
    public function checkCollect($admin_id,$order_id){
        //查询之前的订单
        $orderIds = $this->where('id','<',$order_id)->where('admin_id',0)->where('status',1)->column('id');
        if($orderIds){
            // 判断是否有跳单记录
            $skipNum = Skip::where('order_id','in',$orderIds)->where('admin_id',$admin_id)->count();
            if(count($orderIds) != $skipNum){
                return false;
            }
        }
        return true;

    }

    
    public function getSkipList()
    {
        return ['0' => __('Skip 0'), '1' => __('Skip 1')];
    }

    public function getStatusList()
    {
        return ['1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3'), '4' => __('Status 4'), '5' => __('Status 5'), '6' => __('Status 6')];
    }


    public function getSkipTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['skip']) ? $data['skip'] : '');
        $list = $this->getSkipList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getAccepttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['accepttime']) ? $data['accepttime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getRevoketimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['revoketime']) ? $data['revoketime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getFinishtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['finishtime']) ? $data['finishtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getAppealtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['appealtime']) ? $data['appealtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setAccepttimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setRevoketimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setFinishtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setAppealtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function admin()
    {
        return $this->belongsTo('Admin', 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
