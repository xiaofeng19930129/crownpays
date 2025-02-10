<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Order;
use app\common\model\Member;
use app\common\model\Config;
class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = 'default';

    public function index()
    {


        // $this->view->engine->layout(false);
        // $this->layout = '';
        // return $this->view->fetch();

        $orderList = Order::where('user_id',$this->auth->id)->order('id desc')->paginate(5);
        $firstMember = (new Member)->getUserMember();

        $userMember = (new Member)->getUserMember($this->auth->isLogin() ? $this->auth->member_id : 1);

        $weekMap = [
            __('Day 7'),//'日', 
            __('Day 1'),//'一', 
            __('Day 2'),//'二', 
            __('Day 3'),//'三', 
            __('Day 4'),//'四', 
            __('Day 5'),//'五', 
            __('Day 6'),//'六'
        ];
        $weekDayNum = date('w'); // 获取数字表示的星期几
        $weekDayChinese = $weekMap[$weekDayNum]; // 转换为中文星期
        
        $iConfig = Config::getConfigArray([
            'expense_one_fixed',
            'expense_one_permillage',
            'expense_two',
        ]);
        
        $this->assign('orderList',$orderList);
        $this->assign('firstMember',$firstMember);
        $this->assign('weekDayChinese',$weekDayChinese);
        $this->assign('iConfig',$iConfig);
        $this->assign('userMember',$userMember);
        $this->assignconfig('iConfig',$iConfig);
        $this->assignconfig('userMember',$userMember);
        return $this->view->fetch();
    }
    public function customer(){
        return $this->view->fetch();
    }
}
