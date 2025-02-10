<?php

namespace app\common\validate;

use think\Validate;

class User extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'type'  => 'require',
        'amount'    => 'require|number|gt:0',
    ];

    /**
     * 字段描述
     */
    protected $field = [
        'type' => 'Operation type',
        'amount' => 'Operation amount',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'type.require' => 'Please select the type of operation',
        'amount.require'     => 'Please enter the number of operations',
        'amount.number'     => 'The number of operations must be a number',
        'amount.gt'     => 'The number of operations must be greater than 0',
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'deposit'  => ['type','amount']
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
