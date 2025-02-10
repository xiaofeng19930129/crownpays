<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\User;
use app\common\controller\Backend;
use app\common\model\Attachment;
use fast\Date;
use think\Db;
use app\admin\model\Order;
use app\admin\model\UserMoneyLog;
/**
 * 控制台
 *
 * @icon   fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{
    protected $noNeedRight = ['statistics','ranking'];
    /**
     * 查看
     */
    public function index()
    {
        try {
            \think\Db::execute("SET @@sql_mode='';");
        } catch (\Exception $e) {

        }

        $isSuperAdmin = in_array($this->auth->id,[1,2]);

        $starttime = Date::unixtime('day', -6);
        $endtime = Date::unixtime('day', 0, 'end');
        $dateData = [];
        $where = [];

        $statistics = $this->statistics($isSuperAdmin);
        foreach ($statistics as $key => $value) $this->assign($key,$value);
        
        if(!$isSuperAdmin){
            $where = ['admin_id' => $this->auth->id];
        }

        for ($time = $starttime; $time <= $endtime;) {
            $start = $time;
            $end = $time + 86400;
            $dateData[] = date('Y-m-d',$time);
            $orderData[] = Order::where($where)->where('createtime','between time', [$start, $end])->sum('usdt');
            $time += 86400;
        }

        

        $this->assign('isSuperAdmin',$isSuperAdmin);
        // $this->assign('totalUser',$totalUser);
        // $this->assign('totalUserAdd',$totalUserAdd);
        // $this->assign('totalOrderNum',$totalOrderNum);
        // $this->assign('totalOrderNumAdd',$totalOrderNumAdd);
        // $this->assign('totalOrderMoney',$totalOrderMoney);
        // $this->assign('totalOrderMoneyAdd',$totalOrderMoneyAdd);
        // $this->assign('totalOrderExpense',$totalOrderExpense);
        // $this->assign('totalOrderExpenseAdd',$totalOrderExpenseAdd);
        // $this->assign('totalUserRecharge',$totalUserRecharge);
        // $this->assign('totalUserRechargeAdd',$totalUserRechargeAdd);


        $this->assignconfig('column', $dateData);
        $this->assignconfig('userdata', $orderData);
        
        return $this->view->fetch();
    }
    /**
     * 查看
     */
    public function statistics($isSuperAdmin)
    {
        
        $todayTime = strtotime('today');
        $searchtime = $this->request->request('searchtime');
        $where = [];
        if($searchtime){
            $searchtimeArray = explode(' - ',$searchtime);
            $start = strtotime($searchtimeArray[0]);
            $end = strtotime($searchtimeArray[1]);
            $where['createtime'] = ['between time', [$start, $end]];
        }
        
        if($isSuperAdmin){
            //总用户，今日新增
            $totalUser = User::where($where)->count();
            $totalUserAdd = User::where('createtime','>',$todayTime)->count();

            // 总订单数量，今日新增
            $totalOrderNum = Order::where($where)->count();
            $totalOrderNumAdd = Order::where('createtime','>',$todayTime)->count();

            // 总订单金额，今日新增
            $totalOrderMoney = Order::where($where)->sum('usdt');
            $totalOrderMoneyAdd = Order::where('createtime','>',$todayTime)->sum('usdt');

            // 总手续费，今日新增
            $totalOrderExpense = Order::where($where)->sum('total_expense');
            $totalOrderExpenseAdd = Order::where('createtime','>',$todayTime)->sum('total_expense');

            //总充值，今日新增
            $totalUserRecharge = UserMoneyLog::where($where)->where('source',1)->sum('money');
            $totalUserRechargeAdd = UserMoneyLog::where('source',1)->where('createtime','>',$todayTime)->sum('money');

            $data = [
                'totalUser' => $totalUser,
                'totalUserAdd' => $totalUserAdd,
                'totalOrderNum' => $totalOrderNum,
                'totalOrderNumAdd' => $totalOrderNumAdd,
                'totalOrderMoney' => $totalOrderMoney,
                'totalOrderMoneyAdd' => $totalOrderMoneyAdd,
                'totalOrderExpense' => $totalOrderExpense,
                'totalOrderExpenseAdd' => $totalOrderExpenseAdd,
                'totalUserRecharge' => $totalUserRecharge,
                'totalUserRechargeAdd' => $totalUserRechargeAdd,
            ];
        }else{
            // 总订单数量，今日新增
            $salesmanTotalOrderNum = Order::where($where)->count();
            $salesmanTotalOrderNumAdd = Order::where('createtime','>',$todayTime)->count();

            // 总订单金额，今日新增
            $salesmanTotalOrderMoney = Order::where($where)->sum('usdt');
            $salesmanTotalOrderMoneyAdd = Order::where('createtime','>',$todayTime)->sum('usdt');

            $data = [
                'salesmanTotalOrderNum' => $salesmanTotalOrderNum,
                'salesmanTotalOrderNumAdd' => $salesmanTotalOrderNumAdd,
                'salesmanTotalOrderMoney' => $salesmanTotalOrderMoney,
                'salesmanTotalOrderMoneyAdd' => $salesmanTotalOrderMoneyAdd,
            ];
        }

        


        

       
        if ($this->request->isAjax()) {
            $this->success(__('Operation completed'),'',$data);
        }else{
            return $data;
        }
    }


    public function ranking($domId,$range){

        $where = [];
        if($range == 1){
            //日
            $start = strtotime('today');
            $end = time();
            $where['createtime'] = ['between time', [$start, $end]];
        }elseif($range == 2){
            // 月
            $start = strtotime(date('Y-m-01 00:00:00'));
            $end  = time();
            $where['createtime'] = ['between time', [$start, $end]];
        }elseif($range == 3){
            // 年
            $start = strtotime(date('Y-01-01'));
            // 本年的结束日期
            $end = time();
            $where['createtime'] = ['between time', [$start, $end]];
        }else{
            // 总
        }
        $userModel = new User;
        if($domId == 'list1'){
            //下单排行（数量）
            $list = Order::where($where)->field('user_id, COUNT(*) AS total_count,SUM(usdt) AS total_amount')->group('user_id')->order('total_count desc')->limit(10)->select();
        }elseif($domId == 'list2'){
            //下单排行（金额）
            $list = Order::where($where)->field('user_id, COUNT(*) AS total_count,SUM(usdt) AS total_amount')->group('user_id')->order('total_amount desc')->limit(10)->select();
            
        }elseif($domId == 'list3'){
            // 充值排行
            $where['source'] = 1;
            $list = UserMoneyLog::where($where)->field('user_id,COUNT(*) AS total_count,SUM(money) AS total_amount')->group('user_id')->order('total_amount desc')->limit(10)->select();
            
        }else{
            // 接单排行
            $where['admin_id']= ['>',0];
            $list = Order::where($where)->field('admin_id as user_id,COUNT(*) AS total_count,SUM(usdt) AS total_amount')->group('user_id')->order('total_count desc')->limit(10)->select();
            $userModel = new Admin();
        }
        $newData = [];
            foreach ($list as $key => $item) {
                $newData[] = [
                    'line' => $key+1,
                    'user' => $userModel->where('id',$item['user_id'])->value('username'),
                    'count' => $item['total_count'],
                    'money' => $item['total_amount'],
                ];                
            }
        $this->success(__('Operation completed'),'',$newData);
    }

}
