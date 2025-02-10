define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'member/index' + location.search,
                    add_url: 'member/add',
                    edit_url: 'member/edit',
                    del_url: '',
                    multi_url: '',
                    import_url: '',
                    table: 'member',
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
                search:false,
                commonSearch:false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),},
                        {field: 'level', title: __('Level')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'NGNUSD', title: __('NGNUSD'), operate:'BETWEEN'},
                        {field: 'USDCNY', title: __('USDCNY'), operate:'BETWEEN'},
                        {field: 'NGNCNY', title: __('NGNCNY'), operate:'BETWEEN'},
                        {field: 'naira_stream', title: __('Naira stream'), operate:'BETWEEN'},
                        {field: 'ustd_balance', title: __('Ustd balance'), operate:'BETWEEN'},
                        {field: 'defaults', title: __('Defaults')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
