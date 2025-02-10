<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;

/**
 * 会员余额变动管理
 *
 * @icon fa fa-circle-o
 */
class MoneyLog extends Backend
{

    /**
     * MoneyLog模型对象
     * @var \app\admin\model\MoneyLog
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\UserMoneyLog;

    }

    /**
     * 查看
     */
    public function index($ids=null)
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

            $searchWhere = [];
            if($ids) $searchWhere = ['user_id'=>$ids];

            $list = $this->model
                    ->with(['user','admin'])
                    ->where($where)
                    ->where($searchWhere)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                
                $row->getRelation('user')->visible(['username']);
				$row->getRelation('admin')->visible(['username']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }

        $sourceList = $this->model->getSourceList();

        $this->assignconfig('ids',$ids);
        $this->assignconfig('sourceList',$sourceList);
        return $this->view->fetch();
    }

}
