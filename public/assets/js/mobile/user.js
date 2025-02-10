define(['jquery', 'bootstrap', 'mobile', 'form', 'template'], function ($, undefined, Mobile, Form, Template) {
    var validatoroptions = {
        invalid: function (form, errors) {
            $.each(errors, function (i, j) {
                Layer.msg(j);
            });
        }
    };
    var Controller = {
        index:function(){
            $(document).on("click", ".rechargeNow", function () {
                $("[data-tips-image]").click();
            })
        },
        order: function () {

            $(document).on("click", ".orderRevoke",function(){
                var btnThis = $(this);
                var orderID = btnThis.parent().data('id');
                layer.confirm(__('Confirm cancellation of this order?'), {
                    btn: [__('Confirm'), __('Cancel')] // 按钮
                }, function (index) {
                    Fast.api.ajax({
                        type: 'get',
                        url: '/index/ajax/revoke',
                        data: {order_id:orderID},
                        dataType: 'json',
                    }, function (data, ret) {
                        btnThis.hide();
                        console.log('succ');
                        console.log('data',data);
                        console.log('ret',ret);
                    }, function (data, ret) {
                        
                        console.log('err');

                        console.log('data',data);
                        console.log('ret',ret);
                    });
                    Layer.close(index);
                }, function () {
                    console.log(222);
                });
            })
            $(document).on("click", ".orderAppeal",function(){
                
                var orderID = $(this).parent().data('id');
                var content = Template('appealtpl', {});
                Layer.open({
                    type: 1,
                    title: __('Reset password'),
                    area: ["80%", "80%"],
                    content: content,
                    success: function (layero, index) {
                        layero.find('.appealOrderId').val(orderID);
                        var appealOrderDom = getOrderAppealList(orderID,layero);
                        Form.api.bindevent(layero.find("#appeal-form"), function (data, ret) {
                            layer.close(index);
                        },function(data,ret){
                        });
                    }
                });
            })
            $(document).on("click", ".orderDetail",function(){
                var order_id = $(this).parent().data('id');
                Layer.open({
                    type: 2,
                    title: __('Order detail'),
                    // maxmin: true,
                    area: ['90%', '80%'],
                    content: '/mobile/user/order_detail?ids='+order_id,
                });
            })
            function getOrderAppealList(order_id,layero){
                Fast.api.ajax({url: '/index/ajax/getOrderAppealList', data: {order_id}}, function (data, ret) {
                    console.log('data',data);
                    console.log('ret',ret);

                    var appealOrderHtml = '';
                    for (let i = 0; i < data.length; i++) {
                        appealOrderHtml += '<div class="order-line">';
                        appealOrderHtml += '<div class="item-text flex-1">'+data[i].content+'</div>';
                        appealOrderHtml += '<div class="item-text flex-1">'+(data[i].status == 2 ? data[i].reply_content : '')+'</div>';
                        appealOrderHtml += '<div class="item-text flex-1">'+(data[i].status == 2 ? data[i].replytime : '')+'</div>';
                        appealOrderHtml += '<div class="item-text flex-1">'+(data[i].status == 1 ? __('Waiting for reply') : __('Reply received'))+'</div>';
                        appealOrderHtml += '<div class="item-text flex-1">'+data[i].createtime+'</div>';
                        appealOrderHtml += '</div>';
                        console.log(appealOrderHtml);    
                    }
                    console.log(appealOrderHtml);
                    layero.find('.orderlist').append(appealOrderHtml);
                }, function () {
                    console.log('是爱了');
                });
            }
            $(document).on("click", ".searchBtn",function(){
                var time = $("#time").val();
                window.location.href = "?time="+time;
            })

            $(document).on("click", ".resetBtn",function(){
                var time = $("#time").val();
                window.location.href = "?time=";
            })
            Form.api.bindevent($(".form-horizontal"));
        },
        login: function () {

            //本地验证未通过时提示
            $("#login-form").data("validator-options", validatoroptions);

            //为表单绑定事件
            Form.api.bindevent($("#login-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = ret.url ? ret.url : "/";
                }, 1000);
            });

            //忘记密码
            $(document).on("click", ".btn-forgot", function () {
                var id = "resetpwdtpl";
                var content = Template(id, {});
                Layer.open({
                    type: 1,
                    title: __('Reset password'),
                    area: [$(window).width() < 450 ? ($(window).width() - 10) + "px" : "450px", "355px"],
                    content: content,
                    success: function (layero) {
                        var rule = $("#resetpwd-form input[name='captcha']").data("rule");
                        Form.api.bindevent($("#resetpwd-form", layero), function (data) {
                            Layer.closeAll();
                        });
                        $(layero).on("change", "input[name=type]", function () {
                            var type = $(this).val();
                            $("div.form-group[data-type]").addClass("hide");
                            $("div.form-group[data-type='" + type + "']").removeClass("hide");
                            $('#resetpwd-form').validator("setField", {
                                captcha: rule.replace(/remote\((.*)\)/, "remote(" + $(this).data("check-url") + ", event=resetpwd, " + type + ":#" + type + ")")
                            });
                            $(".btn-captcha").data("url", $(this).data("send-url")).data("type", type);
                        });
                    }
                });
            });
        },
        register: function () {
            //本地验证未通过时提示
            $("#register-form").data("validator-options", validatoroptions);

            //为表单绑定事件
            Form.api.bindevent($("#register-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = ret.url ? ret.url : "/";
                }, 1000);
            }, function (data) {
                $("input[name=captcha]").next(".input-group-btn").find("img").trigger("click");
            });
        },
        changepwd: function () {
            //本地验证未通过时提示
            $("#changepwd-form").data("validator-options", validatoroptions);

            //为表单绑定事件
            Form.api.bindevent($("#changepwd-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = ret.url ? ret.url : "/";
                }, 1000);
            });
        },
        profile: function () {
            // 给上传按钮添加上传成功事件
            $("#faupload-avatar").data("upload-success", function (data) {
                var url = Fast.api.cdnurl(data.url);
                $(".profile-user-img").prop("src", url);
                Toastr.success(__('Uploaded successful'));
            });
            Form.api.bindevent($("#profile-form"));
            $(document).on("click", ".btn-change", function () {
                var that = this;
                var id = $(this).data("type") + "tpl";
                var content = Template(id, {});
                Layer.open({
                    type: 1,
                    title: "修改",
                    area: [$(window).width() < 450 ? ($(window).width() - 10) + "px" : "450px", "355px"],
                    content: content,
                    success: function (layero) {
                        var form = $("form", layero);
                        Form.api.bindevent(form, function (data) {
                            location.reload();
                            Layer.closeAll();
                        });
                    }
                });
            });
        },
        attachment: function () {
            require(['table'], function (Table) {

                // 初始化表格参数配置
                Table.api.init({
                    extend: {
                        index_url: 'user/attachment',
                    }
                });
                var urlArr = [];
                var multiple = Fast.api.query('multiple');
                multiple = multiple == 'true' ? true : false;

                var table = $("#table");

                table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function (e, row) {
                    if (e.type == 'check' || e.type == 'uncheck') {
                        row = [row];
                    } else {
                        urlArr = [];
                    }
                    $.each(row, function (i, j) {
                        if (e.type.indexOf("uncheck") > -1) {
                            var index = urlArr.indexOf(j.url);
                            if (index > -1) {
                                urlArr.splice(index, 1);
                            }
                        } else {
                            urlArr.indexOf(j.url) == -1 && urlArr.push(j.url);
                        }
                    });
                });

                // 初始化表格
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    sortName: 'id',
                    showToggle: false,
                    showExport: false,
                    fixedColumns: true,
                    fixedRightNumber: 1,
                    columns: [
                        [
                            {field: 'state', checkbox: multiple, visible: multiple, operate: false},
                            {field: 'id', title: __('Id'), operate: false},
                            {
                                field: 'url', title: __('Preview'), formatter: function (value, row, index) {
                                    var html = '';
                                    if (row.mimetype.indexOf("image") > -1) {
                                        html = '<a href="' + row.fullurl + '" target="_blank"><img src="' + row.fullurl + row.thumb_style + '" alt="" style="max-height:60px;max-width:120px"></a>';
                                    } else {
                                        html = '<a href="' + row.fullurl + '" target="_blank"><img src="' + Fast.api.fixurl("ajax/icon") + "?suffix=" + row.imagetype + '" alt="" style="max-height:90px;max-width:120px"></a>';
                                    }
                                    return '<div style="width:120px;margin:0 auto;text-align:center;overflow:hidden;white-space: nowrap;text-overflow: ellipsis;">' + html + '</div>';
                                }
                            },
                            {
                                field: 'filename', title: __('Filename'), formatter: function (value, row, index) {
                                    return '<div style="width:150px;margin:0 auto;text-align:center;overflow:hidden;white-space: nowrap;text-overflow: ellipsis;">' + Table.api.formatter.search.call(this, value, row, index) + '</div>';
                                }, operate: 'like'
                            },
                            {field: 'imagewidth', title: __('Imagewidth'), operate: false},
                            {field: 'imageheight', title: __('Imageheight'), operate: false},
                            {field: 'mimetype', title: __('Mimetype'), formatter: Table.api.formatter.search},
                            {field: 'createtime', title: __('Createtime'), width: 120, formatter: Table.api.formatter.datetime, datetimeFormat: 'YYYY-MM-DD', operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                            {
                                field: 'operate', title: __('Operate'), width: 85, events: {
                                    'click .btn-chooseone': function (e, value, row, index) {
                                        Fast.api.close({url: row.url, multiple: multiple});
                                    },
                                }, formatter: function () {
                                    return '<a href="javascript:;" class="btn btn-danger btn-chooseone btn-xs"><i class="fa fa-check"></i> ' + __('Choose') + '</a>';
                                }
                            }
                        ]
                    ]
                });

                // 选中多个
                $(document).on("click", ".btn-choose-multi", function () {
                    Fast.api.close({url: urlArr.join(","), multiple: multiple});
                });

                // 为表格绑定事件
                Table.api.bindevent(table);
                require(['upload'], function (Upload) {
                    Upload.api.upload($("#toolbar .faupload"), function () {
                        $(".btn-refresh").trigger("click");
                    });
                });

            });
        },
        member: function(){
            // 储蓄
            $(document).on("click", ".savingsBtn",function(){
                var id = "deposittpl";
                var content = Template(id, {});
                Layer.open({
                    type: 1,
                    title: __('Savings Treasure'),
                    area: [$(window).width() < 450 ? ($(window).width() - 10) + "px" : "450px", "355px"],
                    content: content,
                    success: function (layero) {
                        // var rule = $("#deposit-form input[name='captcha']").data("rule");
                        Form.api.bindevent($("#deposit-form", layero), function (data) {
                            window.parent.location.reload();
                            Layer.closeAll();
                        });
                    }
                });
            })
        },
        cash: function(){
            // 储蓄
            $(document).on("click", ".withdrawal",function(){
                var id = "withdrawaltpl";
                var content = Template(id, {});
                Layer.open({
                    type: 1,
                    title: __('Withdrawal'),
                    area: [$(window).width() < 450 ? ($(window).width() - 10) + "px" : "600px", "355px"],
                    content: content,
                    success: function (layero) {
                        // var rule = $("#deposit-form input[name='captcha']").data("rule");
                        Form.api.bindevent($("#withdrawal-form", layero), function (data) {
                            console.log('data',data);
                            window.parent.location.reload();
                            Layer.closeAll();
                        });
                    }
                });
            })
        }
    };
    return Controller;
});
