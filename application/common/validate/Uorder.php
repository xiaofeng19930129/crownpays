<?php

namespace app\common\validate;

use think\Validate;

class Uorder extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'amount'    => 'require|number|gt:0',
    ];

    /**
     * 字段描述
     */
    protected $field = [
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'amount.require'     => 'Please enter the recharge amount',
        'amount.number'     => 'The recharge quantity must be a number',
        'amount.gt'     => 'The recharge quantity must be greater than 0',
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['amount'],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        // $this->field = [
        //     'content' => 'Order content',
        //     'naira' => 'Naira num',
        // ];
        parent::__construct($rules, $message, $field);
    }

}
