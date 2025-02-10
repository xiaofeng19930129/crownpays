<?php

namespace app\common\model;

use think\Model;
/**
 * 用户会员
 */
class Member extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [];

    public function getNextLevel($level){
        $member = $this->where('level','>',$level)->field(
            'level,name,NGNUSD,USDCNY,NGNCNY,naira_stream,usdt_balance'
        )->order('level asc')->find();
        return $member;
    }

    public function getUserMember($id=1)
    {
        $member = $this->where('id',$id)->field(
            'level,name,NGNUSD,USDCNY,NGNCNY'
        )->find();
        return $member;
    }
}
