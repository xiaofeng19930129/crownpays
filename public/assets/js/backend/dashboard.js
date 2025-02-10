// define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {
define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template', 'echarts', 'echarts-theme'], function ($, undefined, Backend, Table, Form, Template, Echarts) {

    var Controller = {
        index: function () {


            Form.api.bindevent($(".form-commonsearch"));

            // 基于准备好的dom，初始化echarts实例
            var myChart = Echarts.init(document.getElementById('echart'), 'walden');

            // 指定图表的配置项和数据
            var option = {
                title: {
                    text: '',
                    subtext: ''
                },
                color: [
                    "#18d1b1",
                    "#3fb1e3",
                    "#626c91",
                    "#a0a7e6",
                    "#c4ebad",
                    "#96dee8"
                ],
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: [__('Order amount (USDT)')]
                },
                toolbox: {
                    show: false,
                    feature: {
                        magicType: {show: true, type: ['stack', 'tiled']},
                        saveAsImage: {show: true}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: Config.column
                },
                yAxis: {},
                grid: [{
                    left: 'left',
                    top: 'top',
                    right: '10',
                    bottom: 30
                }],
                series: [{
                    name: __('Order amount (USDT)'),
                    type: 'line',
                    smooth: true,
                    areaStyle: {
                        normal: {}
                    },
                    lineStyle: {
                        normal: {
                            width: 1.5
                        }
                    },
                    data: Config.userdata
                }]
            };

            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);

            $(window).resize(function () {
                myChart.resize();
            });

            $(document).on("click", ".btn-success", function () {
                var searchtime = $("#searchtime").val();
                if(searchtime != ''){
                    Fast.api.ajax({
                        url: 'dashboard/statistics',
                        dataType: 'json',
                        data: {searchtime: searchtime}
                    },function (ret) {
                        $(".totalUser").html(ret.totalUser);
                        $(".totalUserRecharge").html(ret.totalUserRecharge);
                        $(".totalOrderNum").html(ret.totalOrderNum);
                        $(".totalOrderMoney").html(ret.totalOrderMoney);
                        $(".totalOrderExpense").html(ret.totalOrderExpense);
                    }, function (ret) {
                        Toastr.error(ret.msg);
                    });
                }
            });
            
            $(document).ready(function() {
                $(".curtag1").click();
            });

            $(".tags-box").on("click", "span", function () {
                $(this).siblings('.tags-box-span').removeClass('curtag1');
                // 给当前点击的元素添加 'active' 类
                $(this).addClass('curtag1');

                var domId = $(this).parent().attr('id');
                var range = $(this).data('range');
                
                
                Fast.api.ajax({
                    url: 'dashboard/ranking',
                    dataType: 'json',
                    method:'GET',
                    data: {domId,range}
                },function (ret) {

                    var domHtml = '';
                    for (let i = 0;  i< ret.length; i++) {
                        domHtml += '<div class="content-box">';
                        domHtml += '<div class="title-1">' + ret[i].line + '</div>';
                        domHtml += '<div class="title-2">'+ ret[i].user + '</div>';
                        domHtml += '<div class="title-3">'+ ret[i].count +'</div>';
                        domHtml += '<div class="title-4">'+ ret[i].money +'</div>';
                        domHtml += '</div>';               
                    }
                    console.log("." + domId +" .overflow-box");
                    console.log(domHtml);
                    $("." + domId +" .overflow-box").html(domHtml);
                    
                }, function (ret) {
                    // Toastr.error(ret.msg);
                });
                
            });
            
            // -----------------------------------------------------
            $(document).on("click", ".charts-custom a[data-toggle=\"tab\"]", function () {
                var that = this;
                setTimeout(function () {
                    var id = $(that).attr("href");
                    var chart = Echarts.getInstanceByDom($(id)[0]);
                    chart.resize();
                }, 0);
            });
        },
      
    };

    return Controller;
});
