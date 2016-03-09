var Message = {
    index: function () {
        $(document).ready(function () {
            Message.del_all();
            Message.date();
            Message.getSelectChecked();
            $('.view-message').click(function(){
                var messID = $(this).attr('rel');
                $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/message/edit',
                    cache: false,
                    dataType: 'json',
                    data: {
                        'messID': messID,
                    },
                    success: function (result) {
                        if(result.st != 1){
                            bootbox.alert('<b>' + result.ms + '</b>');
                            return false;
                        }else{
                            window.location.href = result.urlRedirect;
                        }
                    }
                });
            });
        });
    },
    getSelectChecked: function () {
        var selectStatus = true;
        $('#StatusID').change(function () {
            arrStatus = $(this).val();
            if ($.inArray("", arrStatus) > -1) {
                $(this).selectpicker('selectAll');
                selectStatus = false;
            } else {
                if (selectStatus == false) {
                    $(this).selectpicker('deselectAll');
                    selectStatus = true;
                }
            }

            var Status = '';
            arrStatus = $(this).val();
            if (jQuery.isEmptyObject(arrStatus) == false) {
                var i = 0;
                $.each(arrStatus, function (key, value) {
                    Status = Status + value;
                    i += 1;
                    if (arrStatus.length != i)
                        Status = Status + ',';
                });
            }
            $('#Status').val(Status);
        });
        var selectSale = true;
        $('#Sales_id').change(function () {
            var arrSales = $(this).val();
            if ($.inArray("0", arrSales) > -1) {
                $(this).selectpicker('selectAll');
                selectSale = false;
            } else {
                if (selectSale == false) {
                    $(this).selectpicker('deselectAll');
                    selectSale = true;
                }
            }

            var Sales = '';
            arrSales = ($(this).val());
            if (jQuery.isEmptyObject(arrSales) == false) {
                var i = 0;
                $.each(arrSales, function (key, value) {
                    Sales = Sales + value;
                    i += 1;
                    if (arrSales.length != i)
                        Sales = Sales + ',';
                });
            }
            $('#Sales').val(Sales);
        });
    },
    del_all: function () {
        $(document).ready(function () {
            $('.delete-all').click(function () {
                var id = [];
                $(".data-id").find("input:checked").each(function (i, tag) {
                    id[i] = $(tag).val();
                });
                if (id.length < 1) {
                    bootbox.alert('<b>Vui lòng chọn ít nhất một sản phẩm để xóa</b>', function () {
                    });
                    return;
                }
                bootbox.confirm('<b>Bạn có muốn xóa những Sản phẩm này không ???</b>', function (result) {
                    if (result) {
                        $.ajax({
                            type: "POST",
                            url: baseurl + '/backend/message/delete',
                            cache: false,
                            dataType: 'json',
                            data: {'mess_id': id},
                            success: function (result) {
                                if (result.success === 1) {
                                    window.location = window.location.href;
                                } else if (result.error === 1) {
                                    bootbox.alert('<b>' + result.message + '</b>');
                                }
                            }
                        });
                    }
                });
            });
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
        }).data('datepicker');
        var checkout = $('.dpd2').datepicker({
            onRender: function (date) {
                return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
            }
        }).on('changeDate', function (ev) {
            checkout.hide();
        }).data('datepicker');
    }
}


