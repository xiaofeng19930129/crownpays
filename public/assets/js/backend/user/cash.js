define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/cash/index' + location.search,
                    // add_url: 'user/cash/add',
                    // edit_url: 'user/cash/edit',
                    del_url: 'user/cash/del',
                    // multi_url: 'user/cash/multi',
                    // import_url: 'user/cash/import',
                    table: 'user_cash',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate:false},
                        {field: 'admin.username', title: __('Admins'), operate: false},
                        {field: 'user.username', title: __('Users'), operate:false},
                        {field: 'user_id', title: __('Username'),visible: false, addClass: "selectpage", extend: "data-source='user/user/selectpage' data-field='username'"},
                        {field: 'admin_id', title: __('Admins'), visible: false, addClass: "selectpage", extend: "data-source='auth/admin/selectpage' data-field='username'"},
                        {field: 'usdt', title: __('Usdt'), operate:false},
                        {field: 'wallet_address', title: __('Wallet_address'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'audit', title: __('Audit status'), searchList: {"1":__('Audit 1'),"2":__('Audit 2'),"3":__('Audit 3')}, formatter: Table.api.formatter.normal},
                        {field: 'audit_remark', title: __('Audit_remark'), operate: false},
                        {field: 'audit_time', title: __('Audit_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'appeal',
                                    text: __('Audit'),
                                    title: __('Audit'),
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa fa-calendar-check-o',
                                    url: 'user/cash/audit',
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        if(row.audit == 1){
                                            return true;
                                        }
                                        // return false;
                                    }
                                },
                                {
                                    name: 'del',
                                    icon: 'fa fa-trash',
                                    title: __('Del'),
                                    extend: 'data-toggle="tooltip" data-container="body"',
                                    classname: 'btn btn-xs btn-danger btn-delone'
                                },
                            ],
                            formatter: Table.api.formatter.buttons
                        }
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
        audit: function () {
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
