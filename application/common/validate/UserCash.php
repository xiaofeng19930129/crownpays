<?php

namespace app\common\validate;

use think\Validate;

class UserCash extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'amount'    => 'require|number|gt:0',
        'wallet_address'  => 'require',

    ];

    /**
     * 字段描述
     */
    protected $field = [
        'amount' => 'Withdrawal quantity',
        'wallet_address' => 'Wallet address',

    ];
    /**
     * 提示消息
     */
    protected $message = [
        'amount.require'     => 'Please enter the withdrawal amount',
        'amount.number'     => 'The withdrawal quantity must be a number',
        'amount.gt'     => 'The number of operations must be greater than 0',
        'wallet_address.require' => 'Please enter your wallet address',
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'withdrawal'  => ['amount','wallet_address']
    ];


}
