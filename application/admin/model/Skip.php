<?php

namespace app\admin\model;

use think\Model;

class Skip extends Model
{


    // 表名
    protected $name = 'skip';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';

    // 追加属性
    protected $append = [];
    
}
