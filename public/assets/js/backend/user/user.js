define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    add_url: 'user/user/add',
                    edit_url: 'user/user/edit',
                    del_url: 'user/user/del',
                    multi_url: 'user/user/multi',
                    table: 'user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'user.id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), sortable: true,operate:false},
                        {field: 'pid', title: __('Pid'),visible: false, addClass: "selectpage", extend: "data-source='user/user/selectpage' data-field='username'"},
                        {field: 'pname', title: __('Pid'),operate: false},
                        {field: 'member.name', title: __('Member Level'),operate:false},
                        {field: 'username', title: __('Username'), operate: 'LIKE'},
                        {field: 'avatar', title: __('Avatar'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false},
                        {field: 'usdt', title: __('USDT'), operate: false, sortable: true},
                        {field: 'deposit', title: __('Deposit'), operate: false, sortable: true},
                        {field: 'current_naira', title: __('Current naira stream'), operate: false, sortable: true},
                        {field: 'total_naira', title: __('Total naira stream'), operate: false, sortable: true},
                        {field: 'agent', title: __('Agent'), formatter: Table.api.formatter.status, searchList: {0: __('Not agent'), 1: __('Is agent')}},
                        {field: 'agenttime', title: __('Become/Cancel agent Time'), formatter: Table.api.formatter.datetime, operate: false, addclass: 'datetimerange', sortable: true},
                        {field: 'logintime', title: __('Logintime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {field: 'jointime', title: __('Jointime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status, searchList: {normal: __('Normal'), hidden: __('Hidden')}},
                        {
                            field: 'buttons',
                            width: "120px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'moneylog',
                                    text: __('Balance record'),
                                    title: __('Balance record'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-money',
                                    url: 'user/money_log/index?ids',
                                    // callback: function (data) {
                                    //     Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    // },
                                    // visible: function (row) {
                                    //     //返回true时按钮显示,返回false隐藏
                                    //     return true;
                                    // }
                                }
                            ],
                            formatter: Table.api.formatter.buttons
                        },
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,operate:false}
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
        agent: function () {
            Controller.api.bindevent();
        },
        recharge: function () {
            Controller.api.bindevent();
        },
        deduction: function () {
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