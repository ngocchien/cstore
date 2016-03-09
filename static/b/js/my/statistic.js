var Statistic = {
    index: function () {
        Statistic.loadPage();
        Statistic.shipping();
        Statistic.date();
        Statistic.getDate();
    },
    loadAjax: function (url, data, position) {
        $.ajax({
            type: "POST",
            url: url,
            cache: false,
            data: data,
            beforeSend: function () {
                $('.loadReport').show();
            },
            success: function (result) {
                if (result) {
                    $(position).html(result);
                } else {
                    alert('<b>Xảy ra lỗi trong quá trình xử lý ...</b>');
                }
            },
            complete: function () {
                $('.loadReport').hide();
            },
        });
    },
    loadPage: function () {
        $("#sales").off("click").on("click", function () {
            var url = baseurl + '/backend/statistic/revenue';
            var from = $('#main-from').val();
            var to = $('#main-to').val();
            var data = {from: from, to: to};
            var position = '.report';
            Statistic.loadAjax(url, data, position);
            //$('.report').load(baseurl + '/backend/statistic/revenue')
        });

        $("#status-orders").off("click").on("click", function () {
            var url = baseurl + '/backend/statistic/status';
            var from = $('#main-from').val();
            var to = $('#main-to').val();
            var data = {from: from, to: to};
            var position = '.report';
            Statistic.loadAjax(url, data, position);
            //$('.report').load(baseurl + '/backend/statistic/status');
        });
        $("#ship").off("click").on("click", function () {
            var url = baseurl + '/backend/statistic/ship';
            var from = $('#main-from').val();
            var to = $('#main-to').val();
            var data = {from: from, to: to};
            var position = '.report';
            Statistic.loadAjax(url, data, position);
            //$('.report').load(baseurl + '/backend/statistic/ship')
        });
        $("#product").off("click").on("click", function () {
            var url = baseurl + '/backend/statistic/product';
            var from = $('#main-from').val();
            var to = $('#main-to').val();
            console.log(from,to);
            var data = {from: from, to: to};
            var position = '.report';
            Statistic.loadAjax(url, data, position);
            //$('.report').load(baseurl + '/backend/statistic/product')
        });
        $(".sellbest").off("click").on("click", function () {
            var url = baseurl + '/backend/statistic/getproduct';
            var position = '.show-prod';
            var from = $('#main-from').val();
            var to = $('#main-to').val();
            var condition = $('#sellbest').val();
            var data = {condition: condition,from: from, to: to, name: "best"};
            Statistic.loadAjax(url, data, position);
            //$('.show-prod').load(baseurl + '/backend/statistic/getproduct', {listPro: list, name: "best"})
        });
        $(".sellslow").off("click").on("click", function () {
            var condition = $('#sellslow').val();
            var from = $('#main-from').val();
            var to = $('#main-to').val();
            var url = baseurl + '/backend/statistic/getproduct';
            var data = {condition: condition,from: from, to: to, name: "slow"};
            var position = '.show-prod';
            Statistic.loadAjax(url, data, position);
            // $('.show-prod').load(baseurl + '/backend/statistic/getproduct', {listPro: list, name: "slow"})
        });
        $(".sellno").off("click").on("click", function () {
            var condition = $('#sellno').val();
            var from = $('#main-from').val();
            var to = $('#main-to').val();
            var url = baseurl + '/backend/statistic/getproduct';
            var data = {condition: condition,from: from, to: to, name: "no"};
            var position = '.show-prod';
            ;
            Statistic.loadAjax(url, data, position);
            //$('.show-prod').load(baseurl + '/backend/statistic/getproduct', {listPro: list, name: "no"})
        });
        $("#cusReturn").off("click").on("click", function () {
            var url = baseurl + '/backend/statistic/cusreturn';
            var from = $('#main-from').val();
            var to = $('#main-to').val();
            var data = {from: from, to: to};
            var position = '.report';
            Statistic.loadAjax(url, data, position);
            //$('.report').load(baseurl + '/backend/statistic/cusreturn');
        });
        $('#home_report').off("click").on("click", function () {
            Statistic.chart_order();
        });
    },
    date: function () {
        var checkin = $('.dpd1').datepicker({
            onRender: function (date) {
                return date.valueOf() < now.valueOf() ? 'disabled' : '';
            }
        }).on('changeDate', function (ev) {
            if (ev.date.valueOf() > checkout.date.valueOf()) {
                var newDate = new Date(ev.date)
                newDate.setDate(newDate.getDate() + 1);
                checkout.setValue(newDate);
            }
            checkin.hide();
            $('.dpd2')[0].focus();
            setTimeout(function () {
                $("#main-from").val($('.dpd1').val());
            }, 300)

        }).data('datepicker');
        var checkout = $('.dpd2').datepicker({
            onRender: function (date) {
                return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
            }
        }).on('changeDate', function (ev) {
            checkout.hide();
            setTimeout(function () {
                $("#main-to").val($('.dpd2').val());
            }, 300)
        }).data('datepicker');
    },
    getDate: function () {
        $('.btn-daterandge').off("click").on("click", function () {
            var from = $('#main-from').val();
            var to = $('#main-to').val();
            var url = baseurl + '/backend/statistic/revenue';
            var data = {from: from, to: to};
            var position = '.report';
            Statistic.loadAjax(url, data, position);
            //$('.report').load(baseurl + '/backend/statistic/revenue/', {from: from, to: to})
        });
        $('.btn-rangeStatus').off("click").on("click", function () {
            var from = $('#main-from').val();
            var to = $('#main-to').val();
            var url = baseurl + '/backend/statistic/status';
            var data = {from: from, to: to};
            var position = '.report';
            Statistic.loadAjax(url, data, position);
            // $('.report').load(baseurl + '/backend/statistic/status/', {from: from, to: to})
        });
        $('.btn-statusReturn').off("click").on("click", function () {
            var from = $('#main-from').val();
            var to = $('#main-to').val();
            var url = baseurl + '/backend/statistic/cusreturn';
            var data = {from: from, to: to};
            var position = '.report';
            Statistic.loadAjax(url, data, position);
            //$('.report').load(baseurl + '/backend/statistic/cusreturn/', {from: from, to: to})
        });
        $('.btn-dateOrder').off("click").on("click", function () {
            var from = $('#main-from').val();
            var to = $('#main-to').val();
            var url = baseurl + '/backend/statistic/cusreturn';
            var data = {from: from, to: to};
            var position = '.report';
            Statistic.loadAjax(url, data, position);
            $('.report').load(baseurl + '/backend/statistic/cusreturn/', {from: from, to: to})
        });
        $('.btn-prod').off("click").on("click", function () {
            var from = $('#main-from').val();
            var to = $('#main-to').val();
            var url = baseurl + '/backend/statistic/product';
            var data = {from: from, to: to};
            var position = '.report';
            Statistic.loadAjax(url, data, position);
            //$('.report').load(baseurl + '/backend/statistic/product/', {from: from, to: to})
        });
    },
    shipping: function () {
        $('.payment').off("click").on("click", function () {
            $.ajax({
                url: baseurl + '/backend/statistic/method-payment',
                type: "POST",
                dataType: 'json',
                beforeSend: function () {
                    $('.loadReport').show();
                },
                success: function (result) {
                    if (result.st == 1) {
                        $('.report').html(result.data);
                        console.log(result['dataChart']);
                        $('.chart-ship-pie').highcharts({
                            chart: {
                                plotBackgroundColor: null,
                                plotBorderWidth: null,
                                plotShadow: false,
                                type: 'pie'
                            },
                            title: {
                                text: 'Thống kê giữa các phương thức thanh toán'
                            },
                            tooltip: {
                                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                            },
                            plotOptions: {
                                pie: {
                                    allowPointSelect: true,
                                    cursor: 'pointer',
                                    dataLabels: {
                                        enabled: true,
                                        format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                        style: {
                                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                        }
                                    }
                                }
                            },
                            series: [{
                                    name: "tỉ lệ đơn hàng",
                                    colorByPoint: true,
                                    data: result['dataChart'],
                                }]
                        });
//                        
                    }
                },
                complete: function () {
                    $('.loadReport').hide();
                }
            })
        })
    },
    status: function () {
        $(document).ready(function () {
            $('.btn-export-status').click(function () {
                var dfrom = $.trim($('.dpd1').val());
                var dto = $.trim($('.dpd2').val());
                window.location.href = baseurl + '/backend/export/exportstatus?from=' + dfrom + '&to=' + dto;
            });
        });
    },
    Chart_ship: function () {
        $.ajax({
            url: baseurl + '/backend/statistic/shipChart',
            type: "POST",
            dataType: 'json',
            success: function (point) {
                console.log(point['data']['pie']);

                $('.chart-ship-columm').highcharts({
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: 'Thống kê vận chuyển'
                    },
                    xAxis: {
                        categories: point['data']['cate'],
                        crosshair: true
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Đơn hàng'
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                '<td style="padding:0"><b>{point.y:.1f} đơn hàng</b></td></tr>',
                        footerFormat: '</table>',
                        shared: true,
                        useHTML: true
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 0
                        }
                    },
                    series: point['data']['serial']

                });
                $('.chart-ship-pie').highcharts({
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {
                        text: 'Thống kê giữa các hình thức vận chuyển'
                    },
                    tooltip: {
                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            }
                        }
                    },
                    series: [{
                            name: "tỉ lệ đơn hàng",
                            colorByPoint: true,
                            data: point['data']['pie'],
                        }]
                });
            }
        });
    },
    searchProd: function () {
        $('.btn-searchProduct').click(function () {
            var search_product = $('.search_product').val();
            var condition = $('#condition').val();
            var from = $('#main-from').val();
            var to = $('#main-to').val();
            var sort = $('#sort').find(":selected").val();
            console.log(sort);
            var name = $('#name').val();
            $('.show-prod').load(baseurl + '/backend/statistic/getproduct', {condition: condition, name: name, search_name: search_product,sort: sort,from: from, to: to});
        });
    },
    chart_order: function () {
        $.ajax({
            url: baseurl + '/backend/order/getorderforchart',
            type: "POST",
            dataType: 'json',
            success: function (point) {
                $('.report').highcharts({
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: 'Đơn đặt hàng trong 15 ngày gần nhất'
                    },
                    xAxis: {
                        categories: point['data']['cate'],
                        crosshair: true
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Đơn hàng'
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                '<td style="padding:0"><b>{point.y:.1f} đơn hàng</b></td></tr>',
                        footerFormat: '</table>',
                        shared: true,
                        useHTML: true
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 0
                        }
                    },
                    series: point['data']['serial']

                });
            }
        });
    }
}


