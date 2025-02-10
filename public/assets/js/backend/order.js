define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/index' + location.search,
                    add_url: 'order/add',
                    edit_url: '',
                    del_url: 'order/del',
                    multi_url: '',
                    import_url: '',
                    table: 'order',
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
                        {field: 'id', title: __('Id'),operate: false},
                        {field: 'admin.username', title: __('Admins'), operate: false},
                        {field: 'user.username', title: __('Users'), operate:false},
                        {field: 'user_id', title: __('Username'),visible: false, addClass: "selectpage", extend: "data-source='user/user/selectpage' data-field='username'"},
                        {field: 'admin_id', title: __('Admins'), visible: false, addClass: "selectpage", extend: "data-source='auth/admin/selectpage' data-field='username'"},
                        {field: 'sn', title: __('Sn')},
                        {field: 'naira', title: __('Naira'), operate:'BETWEEN'},
                        {field: 'usdt', title: __('Usdt'), operate:'BETWEEN'},
                        {field: 'exchange', title: __('Exchange'), operate:false},
                        {field: 'expense', title: __('Expense'), operate:false},
                        {field: 'bank_charges', title: __('Bank charges'), operate:false},
                        
                        {field: 'voucher', title: __('Voucher'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false},
                        {field: 'fail', title: __('Fail'), searchList: {"0":__('Fail 0'),"1":__('Fail 1'),"2":__('Fail 2')}, formatter: Table.api.formatter.normal},
                        // {field: 'skip', title: __('Skip'), searchList: {"0":__('Skip 0'),"1":__('Skip 1')}, formatter: Table.api.formatter.normal},
                        {field: 'accepttime', title: __('Accepttime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'revoketime', title: __('Revoketime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'finishtime', title: __('Finishtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'appealtime', title: __('Appealtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3'),"4":__('Status 4'),"5":__('Status 5'),"6":__('Status 6')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'collect',
                                    text: __('Receiving orders'),
                                    title: __('Receiving orders'),
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    icon: 'fa fa-magic',
                                    url: 'order/collect',
                                    confirm: __('Confirm acceptance of the order'),
                                    success: function (data, ret) {

                                        //接单完成后返回账户余额，今日接单数量，今日接单金额
                                        console.log('statistics',data);
                                        $(".adminNaira").html(data.adminNaira);
                                        $(".total").html(data.total);
                                        $(".money").html(data.money);

                                        // 成功后刷新父级
                                        table.bootstrapTable('refresh');
                                    },
                                    visible:function(row){
                                        // !Config.isSuperAdmin &&  
                                        if(row.status == 1){
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                                {
                                    name: 'skip',
                                    text: __('Skip order'),
                                    title: __('Skip order'),
                                    classname: 'btn btn-xs btn-warning btn-ajax',
                                    icon: 'fa fa-magic',
                                    url: 'order/skip',
                                    confirm: __('Confirm to skip this order'),
                                    success: function (data, ret) {
                                        // 成功后刷新父级
                                        table.bootstrapTable('refresh');
                                    },
                                    visible:function(row){
                                        if(row.isSkip){
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                                {
                                    name: 'fail',
                                    text: __('Transfer failed'),
                                    title: __('Transfer failed'),
                                    classname: 'btn btn-xs btn-warning btn-ajax',
                                    icon: 'fa fa-magic',
                                    url: 'order/fail',
                                    confirm: __('Confirmation of transfer setting failed'),
                                    success: function (data, ret) {
                                        // 成功后刷新父级
                                        table.bootstrapTable('refresh');
                                    },
                                    visible:function(row){
                                        if(row.fail == 2 && row.status >= 4){
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                                {
                                    name: 'proof',
                                    text: __('Upload credentials'),
                                    title: __('Upload credentials'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-wordpress',
                                    url: 'order/proof',
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        if(row.isUploadVoucher){
                                            return true;
                                        }
                                        return false;
                                    }
                                },{
                                    name: 'appeal',
                                    text: __('Appeal response'),
                                    title: __('Appeal response'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-wordpress',
                                    url: 'order/appeal',
                                    visible: function (row) {
                                        //返回true时按钮显示,返回false隐藏
                                        if(row.status == 5){
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                                {
                                    name: 'details',
                                    text: __('details'),
                                    title: __('details'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    extend:'data-area=["1000px","800px"]',
                                    icon: 'fa fa-cube',
                                    url: 'order/details',
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
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'order/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '140px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'order/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'order/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        automatic:function(){
            // Controller.api.bindevent();
            Form.api.bindevent($("#automatic-form"), function (data, ret) {
                // $(".btnReset").click();
                // $(".totalExpense,.totalNaira,.totalUsdt").text(0);
                console.log('data',data);
                $("#automatic-form").attr('action','order/proof');
                $(".order_sn").text('');
                $(".order_naira").text('');
                $(".order_usdt").text('');
                $(".order_username").text('');
                $(".order_createtime").text('');
                $("#c-content").text('');
                $("#c-voucher").val('');
                $("#c-voucher").change();
                startTimer();
                $(".automatic-box").show(1000);
                return false;
            },function(data,ret){
                console.log('失败');
                
            });


            let timer;
            function startTimer() {
                if (timer) {
                    console.log('Timer is already running.');
                    return;
                }
                timer = setInterval(function() {
                    Fast.api.ajax({
                        url: 'order/automatic?type=2',
                        type: 'get',
                        loading: false,
                    }, function (data, ret) {
                        if(data){
                            $(".automatic-box").hide(1000);
                            stopTimer();
                            $("#automatic-form").attr('action','order/proof?ids='+data.id);
                            $(".order_sn").text(data.sn);
                            $(".order_naira").text(data.naira);
                            $(".order_usdt").text(data.usdt);
                            $(".order_username").text(data.username);
                            $(".order_createtime").text(data.createtime);
                            $("#c-content").text(data.content);
                            
                        }
                        return false;
                    });

                }, 5000);
                // $('#timerStatus').text('Timer is running.');
            }
 
            function stopTimer() {
                if (!timer) {
                    console.log('No timer is running.');
                    return;
                }
                clearInterval(timer);
                timer = null;
            }
 
            startTimer();
 
            // stopTimer();
        },
        add: function () {
            Controller.api.bindevent();
        },
        proof: function () {
            Controller.api.bindevent();
        },
        appeal: function () {
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
