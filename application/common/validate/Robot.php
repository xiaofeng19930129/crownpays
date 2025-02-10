<?php

namespace app\common\validate;

use think\Validate;

class Robot extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'content'  => 'require',
        'groupname'    => 'require',
        'groupid' => 'require',
        'wechatid' => 'require',
    ];

    /**
     * 字段描述
     */
    protected $field = [
        'content'  => '消息内容',
        'groupname'    => '群名',
        'groupid' => '群ID',
        'wechatid' => '个人微信ID',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'content.require'  => '请输入消息内容',
        'groupname.require'    => '请输入群名',
        'groupid.require' => '请输入群ID',
        'wechatid.require' => '请输入个人微信ID',
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'receive'  => ['content','groupname','groupid','wechatid']
    ];

    // public function __construct(array $rules = [], $message = [], $field = [])
    // {
    //     $this->field = [
    //         'content' => 'Order content',
    //         'naira' => 'Naira num',
    //     ];
    //     parent::__construct($rules, $message, $field);
    // }

}
