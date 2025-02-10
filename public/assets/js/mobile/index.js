define(['jquery', 'bootstrap', 'mobile', 'form', 'template'], function ($, undefined, Mobile, Form, Template) {
    var validatoroptions = {
        invalid: function (form, errors) {
            $.each(errors, function (i, j) {
                Layer.msg(j);
            });
        }
    };
    var Controller = {
        index: function () {

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
