<?php

namespace app\common\validate;

use think\Validate;

class Order extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'cardnumber'  => 'require',
        'bankname'  => 'require',
        'naira'    => 'require|number|gt:0',
        'expense_type'    => 'require',
        'appeal_content' => 'require',
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
        'cardnumber.require' => 'Please enter the card number',//请输入卡号
        'bankname.require' => 'Please enter the bank name',//请输入银行名称
        'naira.require'     => 'Please enter the quantity of naira',
        'naira.number'     => 'The input number of naira must be a number',
        'naira.gt'     => 'The number of naira must be greater than 0',
        'expense_type.require' => 'Please select the type of handling fee', 
        'appeal_content.require' => 'Please enter the appeal content'
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['cardnumber','naira','bankname'],
        'appeal' => ['appeal_content'],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'content' => 'Order content',
            'naira' => 'Naira num',
        ];
        parent::__construct($rules, $message, $field);
    }

}
