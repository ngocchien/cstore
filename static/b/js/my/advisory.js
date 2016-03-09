var Advisory = {
    index: function () {
        $(document).ready(function () {
            Advisory.edit();
            Advisory.changeType();
        });
    },
    edit : function () {
        $(document).ready(function () {
            $('.edit').click(function () {
                advisoryID = $(this).attr('rel');
                if (advisoryID) {
                    bootbox.confirm('<b>Bạn đã tư vấn cho số điện thoại này chưa ???</b>', function (result) {
                        if (result) {
                            bootbox.dialog({
                            message: '  <select class="form-control" id="changeType">\n\
                                            <option value="0">Chưa duyệt</option>\n\
                                            <option value="1">Đã mua</option>\n\
                                            <option value="2">Hẹn gọi lại</option>\n\
                                            <option value="3">Chỉ tham khảo</option>\n\
                                            <option value="4">Thông tin sai</option>\n\
                                            <option value="5">Hẹn tới shop</option>\n\
                                        </select>',
                            title: "Lý do tư vấn !",
                            buttons: {
                                success: {
                                    label: "Xác nhận",
                                    className: "btn-success",
                                    callback: function () {
                                        var intType = $('#changeType').val();
                                        $.ajax({
                                            type: "POST",
                                            url: baseurl + '/backend/advisory/edit',
                                            cache: false,
                                            dataType: 'json',
                                            data: {
                                                'advisoryID': advisoryID,
                                                'intType': intType,
                                            },
                                            success: function (result) {
                                                if (result.st === 1) {
                                                    bootbox.alert('<b>' + result.ms + '</b>', function () {
                                                        window.location = window.location.href;
                                                    });
                                                } else if (result.st === -1) {
                                                    bootbox.alert('<b>' + result.ms + '</b>');
                                                }
                                            }
                                        });
                                    }
                                },
                                danger: {
                                    label: "Thoát",
                                    className: "btn-danger",
                                    callback: function () {

                                    }
                                }
                            }
                        });
                            
                            
                            
//                            $.ajax({
//                                type: "POST",
//                                url: baseurl + '/backend/advisory/edit',
//                                cache: false,
//                                dataType: 'json',
//                                data: {'advisoryID': advisoryID},
//                                success: function (result) {
//                                    if (result.st === 1) {
//                                        bootbox.alert('<b>' + result.ms + '</b>', function () {
//                                            window.location = window.location.href;
//                                        });
//                                    } else if (result.st === -1) {
//                                        bootbox.alert('<b>' + result.ms + '</b>');
//                                    }
//                                }
//                            });
                        }
                    });
                }
            });
        });
    },
    
    del: function () {
        $(document).ready(function () {
            $('.remove').click(function () {
                orderID = $(this).attr('rel');
                if (orderID) {
                    bootbox.dialog({
                        message: '  <select name="commContent" id="commContent" class="form-control">\n\
                                    <option value="1">Không xác thực được số điện thoại</option>\n\
                                    <option value="2">Không xác thực được địa chỉ</option>      \n\
                                    <option value="3">Hết hàng trong kho</option>\n\
                                    <option value="4">Sản phẩm đã ngừng kinh doanh</option>\n\
                                    <option value="5">Khách hàng yêu cầu hủy</option>\n\
                                    </select>',
                        title: "Nhập lý do hủy đơn hàng !",
                        buttons: {
                            success: {
                                label: "Xác nhận",
                                className: "btn-success",
                                callback: function () {
                                    $.ajax({
                                        type: "POST",
                                        url: baseurl + '/backend/order/delete',
                                        cache: false,
                                        data: {
                                            'orderID': orderID,
                                            'comm_content': $("#commContent").val()

                                        },
                                        success: function (result) {
                                            result = $.parseJSON(result);
                                            if (result.ms == 1) {
                                                bootbox.confirm(result.ms, function (result) {
                                                    window.location = window.location.href;
                                                })
                                            } else {
                                                bootbox.alert(result.ms, function () {
                                                    window.location = window.location.href;
                                                });
                                            }

                                        }
                                    });
                                }
                            },
                            danger: {
                                label: "Thoát",
                                className: "btn-danger",
                                callback: function () {

                                }
                            }
                        }
                    });
                }
            });
        });
    },
    changeType: function () {
        $('.changeType').change(function () {
            var status = $(this).val();
            var id = $(this).data('id');
          //  alert(status + '-' + id); return false;
            $.ajax({
                type: "POST",
                url: baseurl + '/backend/advisory/edit-type',
                cache: false,
                dataType: 'json',
                data: {
                    'id': id,
                    'status': status
                },
                success: function (result) {
                    if (result.st == 1) {
                        window.location = window.location.href;
                    } else {
                        bootbox.alert(result.ms, function () {
                            window.location = window.location.href;
                        });
                    }
                }
            });

        });
    }
};
