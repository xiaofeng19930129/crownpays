<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 订单返佣管理
 *
 * @icon fa fa-circle-o
 */
class OrderShare extends Backend
{

    /**
     * Share模型对象
     * @var \app\admin\model\OrderShare
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\OrderShare;
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
                    ->with(['order','user'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                $row['parent_name'] = model('User')->where('id',$row['parent_id'])->value('username');
                $row->getRelation('order')->visible(['sn']);
				$row->getRelation('user')->visible(['username']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

}
