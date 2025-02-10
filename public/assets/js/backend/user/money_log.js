define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {

            var index_url = 'user/money_log/index';
            if(Config.ids) index_url += '?ids=' + Config.ids
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    // location.search
                    index_url: index_url,
                    add_url: '',
                    edit_url: '',
                    del_url: '',
                    multi_url: '',
                    import_url: '',
                    table: 'user_money_log',
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
                        {field: 'user_id', title: __('Username'),visible: false, addClass: "selectpage", extend: "data-source='user/user/selectpage' data-field='username'"},
                        {field: 'admin_id', title: __('Admins'), visible: false, addClass: "selectpage", extend: "data-source='auth/admin/selectpage' data-field='username'"},
                        {field: 'user.username', title: __('User.username'), operate: false},
                        {field: 'admin.username', title: __('Admin.username'), operate: false},
                        {field: 'money', title: __('Money'),operate:false},
                        {field: 'before', title: __('Before'),operate:false},
                        {field: 'after', title: __('After'),operate:false},
                        {field: 'source', title: __('Source'),searchList:Config.sourceList,formatter: Table.api.formatter.normal},
                        // {field: 'memo', title: __('Memo'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
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
