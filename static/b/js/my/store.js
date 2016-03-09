var Store = {
    index: function () {
        $(document).ready(function () {
            //Store.date();
            Store.add();
            Store.submitClose();
            $('.btn-del').click(function () {
                var intStoreID = $(this).attr('rel');
                bootbox.dialog({
                    message: 'Xóa hợp đồng này?',
                    title: "Thông báo!",
                    buttons: {
                        danger: {
                            label: "Thoát",
                            className: "btn-danger",
                            callback: function () {
                            }
                        },
                        success: {
                            label: "Xác nhận",
                            className: "btn-success",
                            callback: function () {
                                $.ajax({
                                    type: "POST",
                                    url: baseurl + '/backend/store/delete',
                                    dataType: 'json',
                                    cache: false,
                                    data: {
                                        'storeID': intStoreID
                                    },
                                    success: function (result) {
                                        if (result.st == 1) {
                                            bootbox.alert(result.ms, function () {
                                                window.parent.location = window.parent.location;
                                            });
                                        } else {
                                            bootbox.alert('<b>' + result.ms + '</b>');
                                        }
                                    }
                                });
                            }
                        }
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
    },
    add: function () {
        $(document).ready(function () {
            $('.btn-add').click(function () {
                bootbox.dialog({
                    message: '<div class="new-info-provider">\n\
                                <div class="row">\n\
                                    <label for="provider-code" class="col-lg-2 control-label" value="">Mã NCC</label>\n\
                                    <div class="col-lg-10">\n\
                                        <input type="text" placeholder="Nhập mã nhà cung cấp" class="form-control" name="code" id="code-provider">\n\
                                    </div>\n\
                                </div>\n\
                                <div class="row">\n\
                                    <label for="provider-name" class="col-lg-2 control-label" value="">Tên</label>\n\
                                    <div class="col-lg-10">\n\
                                        <input type="text" placeholder="Nhập tên nhà cung cấp" class="form-control" name="name" id="name-provider">\n\
                                    </div>\n\
                                </div>\n\
                                <div class="row">\n\
                                    <label for="provider-phone" class="col-lg-2 control-label" value="">Phone</label>\n\
                                    <div class="col-lg-10">\n\
                                        <input type="text" placeholder="Nhập số điện thoại nhà cung cấp" class="form-control" name="phone" id="phone-provider">\n\
                                    </div>\n\
                                </div>\n\
                                <div class="row">\n\
                                    <label for="provider-address" class="col-lg-2 control-label" value="">Đia chỉ</label>\n\
                                    <div class="col-lg-10">\n\
                                        <input type="text" placeholder="Nhập địa chỉ nhà cung cấp" class="form-control" name="address" id="address-provider">\n\
                                    </div>\n\
                                </div>\n\
                                 <div class="row">\n\
                                    <label for="provider-email" class="col-lg-2 control-label" value="">Email</label>\n\
                                            <div class="col-lg-10">\n\
                                                <input type="text"  placeholder="Nhập email nhà cung cấp" class="form-control" name="email" id="email-provider">\n\
                                            </div>\n\
                                        </div>\n\
                                        <div class="row">\n\
                                            <label for="provider-fax" class="col-lg-2 control-label" value="">Fax</label>\n\
                                            <div class="col-lg-10">\n\
                                                <input type="text" placeholder="Nhập số fax nhà cung cấp" class="form-control" name="fax" id="fax-provider">\n\
                                            </div>\n\
                                        </div>\n\
                                        <div class="row">\n\
                                            <label for="provider-userbane" class="col-lg-2 control-label" value="">Username</label>\n\
                                            <div class="col-lg-10">\n\
                                                <input type="text"  placeholder="Nhập tên đăng nhập cho nhà cung cấp" class="form-control" name="user" id="user-provider">\n\
                                            </div>\n\
                                        </div>\n\
                                        <div class="row">\n\
                                            <label for="provider-password" class="col-lg-2 control-label" value="">Password</label>\n\
                                            <div class="col-lg-10">\n\
                                                <input type="password"  placeholder="Nhập mật khẩu nhà cung cấp" class="form-control" name="pass" id="pass-provider">\n\
                                            </div>\n\
                                        </div></div>',
                    title: "Thêm nhà cung cấp mới!",
                    buttons: {
                        danger: {
                            label: "Thoát",
                            className: "btn-danger",
                            callback: function () {
                            }
                        },
                        success: {
                            label: "Xác nhận",
                            className: "btn-success",
                            callback: function () {
                                $.ajax({
                                    type: "POST",
                                    url: baseurl + '/backend/provider/add-json',
                                    dataType: 'json',
                                    cache: false,
                                    data: {
                                        'code': $.trim($('#code-provider').val()),
                                        'name': $.trim($('#name-provider').val()),
                                        'phone': $.trim($('#phone-provider').val()),
                                        'email': $.trim($('#email-provider').val()),
                                        'address': $.trim($('#address-provider').val()),
                                        'fax': $.trim($('#fax-provider').val()),
                                        'user': $.trim($('#user-provider').val()),
                                        'pass': $.trim($('#pass-provider').val())
                                    },
                                    success: function (result) {
                                        if (result.st == 1) {
                                            bootbox.alert(result.ms, function () {
                                                window.parent.location = window.parent.location;
                                            });
                                        } else {
                                            bootbox.alert('<b>' + result.ms + '</b>');
                                        }
                                    }
                                });
                            }
                        }
                    }
                });
            });

            $('.btn-check').click(function () {
                var intProdID = $.trim($('#prodID-store').val());
                if (intProdID == '') {
                    bootbox.alert('<b>Vui lòng nhập mã sản phẩm!</b>');
                    return false;
                }
                $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/product/get-detail',
                    cache: false,
                    dataType: 'json',
                    data: {
                        'prodCODE': intProdID
                    },
                    success: function (result) {
                        if (result.st == 1) {
                            $('#prodName-store').val(decodeHTMLEntities(result.arrDetailProduct.prod_name));
                            $('#prodId').val(decodeHTMLEntities(result.arrDetailProduct.prod_id));
                        } else if (result.st == 0) {
                            $('#prodName-store').val('');
                            bootbox.alert('<b>Mã sản phẩm này không tồn tại!</b>');
                            return false;
                        } else {
                            bootbox.alert('<b>' + result.ms + '</b>');
                        }
                    }
                });
            });
        });
    },
    submitClose: function () {
        $(".bt-save").each(function (i, tag) {
            $(tag).mouseenter(function () {
                console.log($(this).attr("name"));
                if ($(this).attr("name") == "save") {
                    $(".is_close").val(0);
                } else if ($(this).attr("name") == "save_close") {
                    $(".is_close").val(1);
                }
            }).mouseleave(function () {
                $(".is_close").val(0);
            });
        });
    },
}
function decodeHTMLEntities(str) {
    var element = document.createElement('div');
    if (str && typeof str === 'string') {
        // strip script/html tags
        str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
        str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
        element.innerHTML = str;
        str = element.textContent;
        element.textContent = '';
    }
    return str;
}

