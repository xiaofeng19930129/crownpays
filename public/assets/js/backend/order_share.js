define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order_share/index' + location.search,
                    add_url: 'order_share/add',
                    edit_url: 'order_share/edit',
                    del_url: 'order_share/del',
                    multi_url: 'order_share/multi',
                    import_url: 'order_share/import',
                    table: 'order_share',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        // {field: 'user_id', title: __('Username'),visible: false, addClass: "selectpage", extend: "data-source='user/user/selectpage' data-field='username'"},
                        // {field: 'admin_id', title: __('Admins'), visible: false, addClass: "selectpage", extend: "data-source='auth/admin/selectpage' data-field='username'"},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'order_id', title: __('order.sn'),visible: false, addClass: "selectpage", extend: "data-source='order/selectpage' data-field='sn'"},
                        {field: 'order.sn', title: __('Order.sn'),operate:false},
                        {field: 'user.username', title: __('User.username'), operate: false},
                        {field: 'user_id', title: __('User_id'),visible: false, addClass: "selectpage", extend: "data-source='user/user/selectpage' data-field='username'"},
                        {field: 'parent_id', title: __('Parent_id'),visible: false, addClass: "selectpage", extend: 'data-source="user/user/selectpage" data-field="username"'},//data-params="{"custom[agent]":1}"
                        {field: 'parent_name', title: __('Parent_id'),operate:false},
                        // {field: 'user_member_id', title: __('User_member_id')},
                        // {field: 'user_member_exchange', title: __('User_member_exchange')},
                        // {field: 'user_member_usdt', title: __('User_member_usdt'), operate:'BETWEEN'},
                        // {field: 'parent_member_id', title: __('Parent_member_id')},
                        // {field: 'parent_member_exchange', title: __('Parent_member_exchange')},
                        // {field: 'parent_member_usdt', title: __('Parent_member_usdt'), operate:'BETWEEN'},
                        {field: 'order_naira', title: __('Order_naira'), operate:'BETWEEN'},
                        {field: 'rake_usdt', title: __('Rake_usdt'), operate:'BETWEEN'},
                        // {field: 'dates', title: __('Dates')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
