define(['jquery', 'bootstrap', 'frontend', 'form', 'template','layer'], function ($, undefined, Frontend, Form, Template,Layer) {
    var validatoroptions = {
        invalid: function (form, errors) {
            $.each(errors, function (i, j) {
                Layer.msg(j);
            });
        }
    };
    var Controller = {
        index: function () {

            $("#cates li").click(function(){

                $("#cates li").removeClass('cur');
                $(this).addClass('cur');
                
            })

            $("#faces li").click(function(){
                $("#faces li").removeClass('cur');
                $(this).addClass('cur');

                var mgr6Value = $(this).find('.mgr6').text();
                $(".orderAmount").text(mgr6Value);

            })

            $(".public-btn-red").click(function(){

                var cardarea = $("#cardarea").val();
                if(cardarea == ''){
                    layer.msg('Please enter Card number');
                    return false;
                }

                layer.msg('Order successfully placed', {
                    time: 30, // 提示显示的时间（毫秒）
                    end: function() {
                        // 在提示关闭后执行的下一步操作
                        
                        layer.open({
                            // 基本层类型：0（信息框，默认）1（页面层）2（iframe层，也就是解析content）3（加载层）4（tips层）
                            type: 1,
                            title: "标题",
                            content: $("#content"),
                            area: ['300px', '300px'],
                            offset: 'auto',
                            shade: 0.3,
                            zIndex: 19891014,
                        });
                    }
                });

            })
            $(".contactService").click(function(){
                window.location.href = 'https://wa.me/2348070977777'
            })

            $(document).on("click", ".btnAnalysis", function () {
                var content = $("#c-content").val();
                if(content != ''){
                    // const inputString = "6540870573,  Moniepoint MFB";
                    const parts = content.split(', ');
                    
                    const cardNumber = parts[0];
                    const bankCode = parts[1];
                    $("#c-cardnumber").val(cardNumber);
                    $("#c-bankname").val(bankCode);
                }
            })

            // $(document).on("click", ".rmbBtn", function () {
            //     Layer.msg(__('Not yet open'));
            // })

            // $(document).on("click", ".usdtBtn", function () {
            //     Layer.msg(__('Please scan the customer service QR code to add customer service'));
            //     $('html, body').scrollTop($(document).height());
            // })

            $(document).on("change", ".expense_type", function () {
                compute();
            })
            $(document).on("input", "#c-naira", function () {
                var naira = $(this).val();
                if(naira){
                    compute();
                }
            })

            function compute(){
                //手续费比例
                var expense_type = $(".expense_type:checked").val();
                var inputNaira = $("#c-naira").val();

                var totalUsdt = 0;
                var totalExpense = 0;
                var totalNaira = 0;

                if(inputNaira != ''){
                    totalNaira  = calc(totalNaira, inputNaira, '+');
                }

                if(expense_type == 1){
                    
                    //固定手续费+百分比
                    totalExpense = calc(totalExpense,Config.iConfig.expense_one_fixed, '+');
                    var permillage = calc(Config.iConfig.expense_one_permillage,1000, '/');
                    var expenseRatio = calc(inputNaira, permillage, '*');
                    totalExpense = calc(totalExpense, expenseRatio, '+');
                    totalNaira  = calc(totalNaira, totalExpense, '+');
                    totalUsdt = calc(totalNaira, Config.userMember.NGNUSD, '/');
                }else{

                    totalNaira = calc(Config.iConfig.expense_two, totalNaira, '+');
                    //固定手续费
                    totalExpense = Config.iConfig.expense_two;
                    totalUsdt = calc(totalNaira, Config.userMember.NGNUSD, '/');
                }
                $(".totalExpense").text(totalExpense);
                $(".totalNaira").text(totalNaira);
                $(".totalUsdt").text(totalUsdt);
            }
            function calc(num1, num2, operator) {
                switch (operator) {
                    case '+':
                        return Math.floor((Number(num1)+Number(num2)).toFixed(6)* 1000) / 1000;;
                    case '-':
                        return Math.floor((Number(num1) - Number(num2)).toFixed(6)* 1000) / 1000;;
                    case '*':
                        return Math.floor((Number(num1) * Number(num2)).toFixed(6)* 1000) / 1000;;
                    case '/':
                        return Math.floor((Number(num1) / Number(num2)).toFixed(6)* 1000) / 1000;;
                    default:
                        return 'Error: Unsupported operator';
                }
            }
            // $("#order-form").data("validator-options", validatoroptions);
            Form.api.bindevent($("#order-form"), function (data, ret) {
                $(".btnReset").click();
                $(".totalExpense,.totalNaira,.totalUsdt").text(0);
            },function(data,ret){
                console.log('ret',ret);
                if(ret.code === 401){
                    setTimeout(function () {
                        location.href = '/index/user/login';
                    },3000);
                }
                
            });
        },
    };
    return Controller;
});
