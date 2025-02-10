<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use Exception;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use app\admin\model\Skip;
use fast\Tree;
use think\Loader;
/**
 * 订单管理
 *
 * @icon fa fa-circle-o
 */
class Order extends Backend
{

    /**
     * Order模型对象
     * @var \app\admin\model\Order
     */
    protected $model = null;
    protected $adminNaira = 0;
    
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Order;
        $this->adminNaira = $this->auth->getAdminNaira();
        $this->view->assign("skipList", $this->model->getSkipList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->with(['admin','user'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit)->each(function($item,$key){
                    $isUploadVoucher = false;
                    // 是否可以上传凭证
                    if($item['status'] == 3 && $item['admin_id'] == $this->auth->id){
                        $isUploadVoucher = true;
                    }

                    //查询一下，这个订单是否已经跳过了，如果没有才可以显示跳过
                    $skipInfo = Skip::where('order_id',$item['id'])->where('admin_id',$this->auth->id)->find();

                    //当前跳过状态
                    $skip = $skipInfo ? 1:0;

                    //默认不可跳过
                    $isSkip = false;
                    //必须是未接单的 && 必须是账户余额不足才可以
                    if($item['status'] == 1 && $item['naira'] > $this->adminNaira){
                        if(!$skipInfo) $isSkip = true;
                    }
                    $item['skip'] = $skip;
                    $item['isSkip'] = $isSkip;
                    $item['isUploadVoucher'] = $isUploadVoucher;

                    if($item['expense_type'] == 1){
                        $item['expense'] = "{$item['expense_fixed']}+{$item['expense_ratio']}={$item['total_expense']}";
                    }else{
                        $item['expense'] = $item['total_expense'];

                    }
                    

                    return $item;
                });

            foreach ($list as $row) {
                
                $row->getRelation('admin')->visible(['username']);
				$row->getRelation('user')->visible(['username']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        // $isSuperAdmin = $this->auth->isSuperAdmin();

        $isSuperAdmin = in_array($this->auth->id,[1,2]);
        $statistics = $this->model->orderPageStatistics($this->auth->id,$isSuperAdmin);
        $statistics['adminNaira'] = $this->adminNaira;
        $this->assign('statistics',$statistics);
        $this->assign('isSuperAdmin',$isSuperAdmin);
        $this->assignconfig('isSuperAdmin',$isSuperAdmin);
        return $this->view->fetch();
    }


    /**
     * 接单
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function collect($ids){
        $row = $this->model->get($ids);

        // if($isSuperAdmin = in_array($this->auth->id,[1,2])){
        //     $this->error(__('Super administrator unable to operate'));
        // }

        if (!$row) {
            $this->error(__('No Results were found'));
        }
        // 查询这个订单之前，有没有未接单且未跳过的
        // if(!$this->model->checkCollect($this->auth->id,$ids)){
        //     请按顺序接单
        //     $this->error(__('Please accept orders in order'));
        // }

        //判断余额
        if( $row['naira'] > $this->adminNaira){
            $this->error(__('Insufficient account balance'));
        }

        if($row['status'] == 1){

        }elseif($row['status'] == 2){
            $this->error(__('The order has been withdrawn'));
        }elseif($row['status'] == 3){
            $this->error(__('This order has been accepted'));
        }else{
            $this->error(__('The order status is incorrect'));
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            $params = [
                'id' => $ids,
                'status' => 3,//已接单
                'admin_id' => $this->auth->id,
                'accepttime' => time(),//接单时间
            ];
            $result = $row->allowField(true)->save($params);

            //扣除账户余额
            \app\admin\model\Admin::naira($this->auth->id,$row['usdt'],2,3);

            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }

        //重新获取一下统计数据

        $statistics = $this->model->orderPageStatistics($this->auth->id);
        $statistics['adminNaira'] = $this->adminNaira;

        $this->assign('statistics',$statistics);
        $this->success(__('Order accepted successfully'),'',$statistics);
    }
    
    /**
     * 转账失败
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function fail($ids){
        $row = $this->model->get($ids);

        // if($isSuperAdmin = in_array($this->auth->id,[1,2])){
        //     $this->error(__('Super administrator unable to operate'));
        // }

        if (!$row) {
            $this->error(__('No Results were found'));
        }


        if($row['status'] < 4){
            $this->error(__('The order status is incorrect'));
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            $params = [
                'id' => $ids,
                'fail' => 1,//转账失败
            ];
            $result = $row->allowField(true)->save($params);

            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }

        //重新获取一下统计数据

        $statistics = $this->model->orderPageStatistics($this->auth->id);
        $statistics['adminNaira'] = $this->adminNaira;

        $this->assign('statistics',$statistics);
        $this->success(__('Order accepted successfully'),'',$statistics);
    }

    /**
     * 跳过订单
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function skip($ids){
        $row = $this->model->get($ids);
        // if($isSuperAdmin = in_array($this->auth->id,[1,2])){
        //     $this->error(__('Super administrator unable to operate'));
        // }
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if($row['status'] == 1){

        }elseif($row['status'] == 2){
            $this->error(__('The order has been withdrawn'));
        }elseif($row['status'] == 3){
            $this->error(__('This order has been accepted'));
        }else{
            $this->error(__('The order status is incorrect'));
        }

        if($row['naira'] <= $this->adminNaira){
            $this->error(__('The balance is sufficient, please accept the order'));
        }

        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            
            $skipModel = new Skip();
            $params = [
                'order_id' => $ids,
                'admin_id' => $this->auth->id,
                'admin_money' => $this->adminNaira
            ];
            $result = $skipModel->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success(__('Order accepted successfully'));
    }

    /**
     * 完成订单
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function proof($ids)
    {
        // if($isSuperAdmin = in_array($this->auth->id,[1,2])){
        //     $this->error(__('Super administrator unable to operate'));
        // }
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }

        //订单状态错误
        if($row['status'] != 3){
            $this->error(__('The order status is incorrect'));
        }

        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            $params['fail'] = 2;//转账成功
            $params['status'] = 4;
            $params['finishtime'] = time();

            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    
    /**
     * 订单详情
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function details($ids){
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        
        $appealList = \app\common\model\Appeal::where('order_id',$ids)->order('id desc')->select();

        $row['user'] = \app\admin\model\User::get($row['user_id']);
        $row['admin'] = \app\admin\model\Admin::get($row['admin_id']);

        $this->view->assign('row', $row);
        $this->view->assign('appealList', $appealList);
        return $this->view->fetch();
    }

    /**
     * 处理申诉
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function appeal($ids){
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }

        //订单状态错误
        if($row['status'] != 5){
            $this->error(__('The order status is incorrect'));
        }

        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            
            \app\common\model\Appeal::where('order_id',$ids)->update([
                'reply_content' => $params['content'],
                'replytime' => time(),
                'status' => 2
            ]);
            $result = $row->allowField(true)->save(['status'=>6]);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    public function automatic($type = 1){
        if($type == 1){
            return $this->view->fetch();
        }else{
            //获取最新订单
            $order = $this->model->where('naira','<=',$this->adminNaira)
                ->where('status',1)
                // ->where("(admin_id = 0 and status = 1) or (admin_id = {$this->auth->id} and status = 1)")
                ->order('id asc')
                ->find();
            if($order){
                $res = $this->model->where('id',$order['id'])->update([
                    'status' => 3,//已接单
                    'admin_id' => $this->auth->id,
                    'accepttime' => time(),//接单时间
                ]);
                if($res=true){
                    $order['username'] = model('User')->where('id',$order['user_id'])->value('username');
                    $order['createtime'] = date('Y-m-d H:i:s',$order['createtime']);
                    $this->success('','',$order);
                }
            }
            
            $this->success('');
        }
    }

    /**
     * 生成查询所需要的条件,排序方式
     * @param mixed   $searchfields   快速查询的字段
     * @param boolean $relationSearch 是否关联查询
     * @return array
     */
    protected function buildparams($searchfields = null, $relationSearch = null)
    {
        $searchfields = is_null($searchfields) ? $this->searchFields : $searchfields;
        $relationSearch = is_null($relationSearch) ? $this->relationSearch : $relationSearch;
        $search = $this->request->get("search", '');
        $filter = $this->request->get("filter", '');
        $op = $this->request->get("op", '', 'trim');
        $sort = $this->request->get("sort", !empty($this->model) && $this->model->getPk() ? $this->model->getPk() : 'id');
        $order = $this->request->get("order", "DESC");
        $offset = $this->request->get("offset/d", 0);
        $limit = $this->request->get("limit/d", 0);
        $limit = $limit ?: 999999;
        //新增自动计算页码
        $page = $limit ? intval($offset / $limit) + 1 : 1;
        if ($this->request->has("page")) {
            $page = $this->request->get("page/d", 1);
        }
        $this->request->get([config('paginate.var_page') => $page]);
        $filter = (array)json_decode($filter, true);
        $op = (array)json_decode($op, true);
        $filter = $filter ? $filter : [];
        
        //如果不是超级管理员，就只查询未接单和自己接的订单
        // if(!$this->auth->isSuperAdmin()){
        //     $filter['admin_id'] = "{$this->auth->id},0";
        //     $op['admin_id'] = "in";
        // }

        $where = [];
        $alias = [];
        $bind = [];
        $name = '';
        $aliasName = '';
        if (!empty($this->model) && $relationSearch) {
            $name = $this->model->getTable();
            $alias[$name] = Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
            $aliasName = $alias[$name] . '.';
        }
        $sortArr = explode(',', $sort);
        foreach ($sortArr as $index => & $item) {
            $item = stripos($item, ".") === false ? $aliasName . trim($item) : $item;
        }
        unset($item);
        $sort = implode(',', $sortArr);
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $where[] = [$aliasName . $this->dataLimitField, 'in', $adminIds];
        }
        if ($search) {
            $searcharr = is_array($searchfields) ? $searchfields : explode(',', $searchfields);
            foreach ($searcharr as $k => &$v) {
                $v = stripos($v, ".") === false ? $aliasName . $v : $v;
            }
            unset($v);
            $where[] = [implode("|", $searcharr), "LIKE", "%{$search}%"];
        }
        $index = 0;
        foreach ($filter as $k => $v) {
            if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $k)) {
                continue;
            }
            $sym = $op[$k] ?? '=';
            if (stripos($k, ".") === false) {
                $k = $aliasName . $k;
            }
            $v = !is_array($v) ? trim($v) : $v;
            $sym = strtoupper($op[$k] ?? $sym);
            //null和空字符串特殊处理
            if (!is_array($v)) {
                if (in_array(strtoupper($v), ['NULL', 'NOT NULL'])) {
                    $sym = strtoupper($v);
                }
                if (in_array($v, ['""', "''"])) {
                    $v = '';
                    $sym = '=';
                }
            }

            switch ($sym) {
                case '=':
                case '<>':
                    $where[] = [$k, $sym, (string)$v];
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                case 'LIKE %...%':
                case 'NOT LIKE %...%':
                    $where[] = [$k, trim(str_replace('%...%', '', $sym)), "%{$v}%"];
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $where[] = [$k, $sym, intval($v)];
                    break;
                case 'FINDIN':
                case 'FINDINSET':
                case 'FIND_IN_SET':
                    $v = is_array($v) ? $v : explode(',', str_replace(' ', ',', $v));
                    $findArr = array_values($v);
                    foreach ($findArr as $idx => $item) {
                        $bindName = "item_" . $index . "_" . $idx;
                        $bind[$bindName] = $item;
                        $where[] = "FIND_IN_SET(:{$bindName}, `" . str_replace('.', '`.`', $k) . "`)";
                    }
                    break;
                case 'IN':
                case 'IN(...)':
                case 'NOT IN':
                case 'NOT IN(...)':
                    $where[] = [$k, str_replace('(...)', '', $sym), is_array($v) ? $v : explode(',', $v)];
                    break;
                case 'BETWEEN':
                case 'NOT BETWEEN':
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr, function ($v) {
                        return $v != '' && $v !== false && $v !== null;
                    })) {
                        continue 2;
                    }
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'BETWEEN' ? '<=' : '>';
                        $arr = $arr[1];
                    } elseif ($arr[1] === '') {
                        $sym = $sym == 'BETWEEN' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, $sym, $arr];
                    break;
                case 'RANGE':
                case 'NOT RANGE':
                    $v = str_replace(' - ', ',', $v);
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr)) {
                        continue 2;
                    }
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'RANGE' ? '<=' : '>';
                        $arr = $arr[1];
                    } elseif ($arr[1] === '') {
                        $sym = $sym == 'RANGE' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $tableArr = explode('.', $k);
                    if (count($tableArr) > 1 && $tableArr[0] != $name && !in_array($tableArr[0], $alias)
                        && !empty($this->model) && $this->relationSearch) {
                        //修复关联模型下时间无法搜索的BUG
                        $relation = Loader::parseName($tableArr[0], 1, false);
                        $alias[$this->model->$relation()->getTable()] = $tableArr[0];
                    }
                    $where[] = [$k, str_replace('RANGE', 'BETWEEN', $sym) . ' TIME', $arr];
                    break;
                case 'NULL':
                case 'IS NULL':
                case 'NOT NULL':
                case 'IS NOT NULL':
                    $where[] = [$k, strtolower(str_replace('IS ', '', $sym))];
                    break;
                default:
                    break;
            }
            $index++;
        }
        if (!empty($this->model)) {
            $this->model->alias($alias);
        }
        $model = $this->model;
        $where = function ($query) use ($where, $alias, $bind, &$model) {
            if (!empty($model)) {
                $model->alias($alias);
                $model->bind($bind);
            }
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    call_user_func_array([$query, 'where'], $v);
                } else {
                    $query->where($v);
                }
            }
        };
        return [$where, $sort, $order, $offset, $limit, $page, $alias, $bind];
    }

    public function selectpage(){
        return parent::selectpage();
    }
}
