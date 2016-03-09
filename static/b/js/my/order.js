var Order = {
    index: function () {
        $(document).ready(function () {
            Order.date();
            Order.edit();
            Order.del();
            Order.delOrder();
            Order.view();
            Order.reset();
            Order.addCommas();
            Order.shipping();
            Order.product();
            $('.sumo-multiple-select-box').SumoSelect({okCancelInMulti: false});
            $('.sumo-select-box').SumoSelect({okCancelInMulti: false, triggerChangeCombined: false});
//            $('#Sales_id').c
            Order.getSelectChecked();
            $('.btn-export-orders').click(function () {
                $('#frm').attr('action', baseurl + '/backend/export/exportorders');
                $('#frm').submit();
                $('#frm').removeAttr('action');
            });
        });
    },
    shipping: function () {
        $('.shipping').on('change', function () {
            value = $(this).val();
            $(".u-shiping .shipID").val(value);
            if (value == 1) {
                $(".u-shiping .blue-bg h1").html("Thông tin Proship");
            } else if (value == 2) {
                $(".u-shiping .blue-bg h1").html("Thông tin GiaoNhan247");
            }
        })

        city = $('#proship_province');
        cityID = city.val();
        cityName = city.find(':selected').text();

        district = $('#proship_district');

        districtName = district.find(':selected').text();
        ward = $('#proship_ward');
        wardID = district.val();
        wardName = district.find(':selected').text();

        city.change(function () {
            cityID = $(this).val();
            cityName = $(this).find(':selected').text();
            $('.proship .cityName').val(cityName);
            if (cityID) {
                $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/district/get-list-dist-proship',
                    cache: false,
                    dataType: 'json',
                    data: {
                        cityID: cityID
                    },
                    success: function (result) {
                        if (result) {
                            district.html('<option value="">-- Chọn Quận / Huyện --</option>');
                            $.each(result, function (key, value) {
                                district.append('<option value="' + value.districtId + '">' + value.districtName + '</option>');
                            });
                        } else {
                            bootbox.alert('Không thể lấy dữ liệu Quận / Huyện');
                        }
                    }
                });
            }
        });

        district.change(function () {
            cityID = $(this).val();
            districtName = $(this).find(':selected').text();
            $('.proship .distName').val(districtName);
            if (cityID) {
                $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/ward/get-list-ward-proship',
                    cache: false,
                    dataType: 'json',
                    data: {
                        districtID: district.val()
                    },
                    success: function (result) {
                        if (result) {
                            ward.html('<option value="">-- Chọn Phường / Xã --</option>');
                            $.each(result, function (key, value) {
                                ward.append('<option value="' + value.wardId + '">' + value.wardName + '</option>');
                            });
                        } else {
                            bootbox.alert('Không thể lấy dữ liệu Quận / Huyện');
                        }
                    }
                });
            }
        });

        ward.change(function () {
            wardName = $(this).find(':selected').text();
            //                            alert(wardName);
            $('.proship .wardName').val(wardName);
        });
    }, loadAddressShip: function () {
        $(document).ready(function () {
            value = $('.shipping').val();
            if (value) {
                if (value == 1) {
                    city = $('#proship_province');
                    cityID = city.val();
                    cityName = city.find(':selected').text();

                    district = $('#proship_district');

                    districtName = district.find(':selected').text();
                    ward = $('#proship_ward');
                    wardID = district.val();
                    wardName = district.find(':selected').text();

                    // city.change(function () {
                    cityID = $(city).val();
                    cityName = $(city).find(':selected').text();
                    $('.proship .cityName').val(cityName);
                    console.log(cityID);
                    if (cityID) {
                        $.ajax({
                            type: "POST",
                            url: baseurl + '/backend/district/get-list-dist-proship',
                            cache: false,
                            dataType: 'json',
                            data: {
                                cityID: _cityID
                            },
                            success: function (result) {
                                if (result) {
                                    district.html('<option value="">-- Chọn Quận / Huyện --</option>');
                                    $.each(result, function (key, value) {

                                        if (_distID == value.districtId) {
                                            district.append('<option selected value="' + value.districtId + '">' + value.districtName + '</option>');
                                            districtName = $(district).find(':selected').text();
                                            $('.proship .distName').val(districtName);
                                            $.ajax({
                                                type: "POST",
                                                url: baseurl + '/backend/ward/get-list-ward-proship',
                                                cache: false,
                                                dataType: 'json',
                                                data: {
                                                    districtID: _distID
                                                },
                                                success: function (result) {
                                                    if (result) {
                                                        ward.html('<option value="">-- Chọn Phường / Xã --</option>');
                                                        //console.log(wardID);
                                                        $.each(result, function (key, value) {
                                                            if (_wardID == value.wardId) {
                                                                ward.append('<option selected value="' + value.wardId + '">' + value.wardName + '</option>');
                                                            } else {
                                                                ward.append('<option value="' + value.wardId + '">' + value.wardName + '</option>');
                                                            }
                                                        });
                                                    } else {
                                                        // bootbox.alert('Không thể lấy dữ liệu Quận / Huyện');
                                                    }
                                                    wardName = $(ward).find(':selected').text();
                                                    $('.proship .wardName').val(wardName);
                                                }
                                            });
                                        } else {
                                            district.append('<option value="' + value.districtId + '">' + value.districtName + '</option>');
                                        }
                                    });
                                } else {
                                    // bootbox.alert('Không thể lấy dữ liệu Quận / Huyện');
                                }
                            }
                        });
                    }

                }

            }
        })
    }, view: function () {
        $(document).ready(function () {
            $(document).on('change', '#comadvisory', function () {
                $('#comadvisoryother').prop('disabled', false);
            });
            $('.btn-out-of-stock').click(function () {
                var _orderID = parseInt($('.order-id').text());
                var _prodID = $('.edit-date-care').attr('rel');
                bootbox.dialog({
                    message: 'Sản phẩm này đã hết hàng?',
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
                                    url: baseurl + '/backend/order/editproductorder',
                                    dataType: 'json',
                                    cache: false,
                                    data: {
                                        'orderID': _orderID,
                                        'prodID': _prodID,
                                        'status': -1
                                    },
                                    success: function (result) {
                                        if (result.st == 1) {
                                            window.parent.location = window.parent.location;
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
            $('.edit-date-care').click(function () {
                var _prodID = $(this).attr('rel');
                var itemName = $('.cf tr#' + _prodID + ' td').eq(1).find('> a').text();
                var user_info = $('.user-infomation').val();
                bootbox.dialog({
                    message: '<p style="text-align:center;">' + itemName + '<br>\n\
                                <!--<input style="width:200px;margin:auto;margin-top: 10px;" type="text" placeholder="Click vào để chọn ngày..." class="form-control dpd1" id="from">-->\n\
                                <input style="width:100px;margin:auto;margin-top: 10px;" type="text" class="form-control intput-date">\n\
                            </p>',
                    title: "Chăm sóc khách hàng!",
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
                                var intDate = $.trim($('.intput-date').val());
                                var _orderID = parseInt($('.order-id').text());
                                /*if ($.trim($('.dpd1').val()) == '') {
                                 bootbox.alert('<b>Vui lòng nhập đúng định dạng ngày: ex: 15/02/2015</b>');
                                 return false;
                                 }*/
                                $.ajax({
                                    type: "POST",
                                    url: baseurl + '/backend/takecareCustomer/edit',
                                    dataType: 'json',
                                    cache: false,
                                    data: {
                                        'orderID': _orderID,
                                        'prodID': _prodID,
                                        //'time': _time,
                                        'intDate': intDate,
                                        'userInfo': user_info,
                                    },
                                    success: function (result) {
                                        if (result.st == 1) {
                                            window.parent.location = window.parent.location;
                                        } else {
                                            bootbox.alert('<b>' + result.ms + '</b>');
                                            return false;
                                        }
                                    }
                                });
                            }
                        }
                    }
                });
                var checkout = $('.dpd1').datepicker({
                    onRender: function (date) {
                        return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
                    }
                }).on('changeDate', function (ev) {
                    checkout.hide();
                }).data('datepicker');
            });
            $('.changeStatusOrder').change(function () {
                var _Value = $(this).val();
                var _OrderCreatedDate = $('.orderCreatedDate').text();
                var _OrderID = parseInt($('.order-id').text());
                var d = new Date();
                var _timeCurrent = d.getDate() + "/" + (d.getMonth() + 1) + "/" + d.getFullYear();

                if (_Value == 2) {
                    bootbox.dialog({
                        message: '<select id="method_shipping" class="form-control">\n\
                                    <option value="3">Chuyển hàng bằng bưu điện</option>\n\
                                     <option value="2">Chuyển hàng bằng GiaoNhan247</option>\n\\n\
                                       <option value="4">Mua Thuốc Tốt tự giao</option>\n\
                                    </select><br/>',
                        title: "Chọn hình thức vận chuyển!",
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
                                    intMethod = $('#method_shipping').val();
                                    if (!intMethod) {
                                        bootbox.alert('Xảy ra lỗi trong quá trình xử lý !');
                                    }
                                    strOrderCreated = '0';
                                    if (_OrderCreatedDate < _timeCurrent) {
                                        strOrderCreated = 1;
                                    }

                                    $.ajax({
                                        type: "POST",
                                        url: baseurl + '/backend/order/update',
                                        dataType: 'json',
                                        cache: false,
                                        data: {
                                            'orderID': _OrderID,
                                            'ispayment': _Value,
                                            'orderCreated': (_OrderCreatedDate < _timeCurrent) ? 1 : 0,
                                            'method_ship': intMethod
                                        },
                                        success: function (result) {
                                            if (result.st == 1) {
                                                window.parent.location = window.parent.location;
                                            } else {
                                                bootbox.alert('<b>' + result.ms + '</b>');
                                                return false;
                                            }
                                        }
                                    });
                                }
                            }
                        }

                    });
//                    if (_OrderCreatedDate < _timeCurrent) {
//                        $.ajax({
//                            type: "POST",
//                            url: baseurl + '/backend/order/update',
//                            cache: false,
//                            data: {(_OrderCreatedDate < _timeCurrent) {
//                        $.ajax({
//                                'orderID': _OrderID,
//                                'orderCreated': _OrderCreatedDate,
//                                'ispayment': _Value,
//                            },
//                            success: function (result) {
//                                result = JSON.parse(result);
//                                if (result.st == 1) {
//                                    window.parent.location = window.parent.location;
//                                } else {
//                                    bootbox.alert('<b>' + result.ms + '</b>');
//                                }
//                            }
//                        });
//                    }
//                    return false;
                } else if (_Value == -1) {
                    bootbox.dialog({
                        message: ' <!-- <select name="comadvisory" id="comadvisory" class="form-control">\n\
                                    <option value="1">Không xác thực được số điện thoại</option>\n\
                                    <option value="2">Không xác thực được địa chỉ !</option>      \n\
                                    <option value="3">Hết hàng trong kho !</option>\n\
                                    <option value="4">Sản phẩm đã ngừng kinh doanh !</option>\n\
                                    <option value="5">Nhận được yêu cầu hủy từ quý khách !</option>\n\
                                    <option value="6">Khác</option>\n\
                                    </select><br/>--> <input value="6" type="hidden" id="comadvisory" name="comadvisory"/>\n\
                                    <textarea name="comadvisoryother" id="comadvisoryother" class="form-control" value="" placeholder="Nhập lý do"></textarea><br/>\n\
                                    <center><p id="mserror" style="color:red;display:none"><b>Nhập lý do hủy đơn hàng! ???</b></p></center>',
                        title: "Lý do hủy đơn hàng !",
                        buttons: {
                            success: {
                                label: "Xác nhận",
                                className: "btn-success",
                                callback: function () {
//                                    if ($('#comadvisory').val() != 6) {
//                                        comment = $('#comadvisory').val();
//                                    } else {
//                                        comment = $('#comadvisoryother').val();
//                                    }
                                    var strComment = $.trim($('#comadvisoryother').val());
                                    //alert(strComment);return false;
                                    if (strComment != '') {
                                        $.ajax({
                                            type: "POST",
                                            url: baseurl + '/backend/order/delete',
                                            cache: false,
                                            dataType: 'json',
                                            data: {
                                                'orderID': _OrderID,
                                                'comm_content': strComment,
                                            },
                                            beforeSend: function () {
                                                bootbox.alert('<b>Đang trong quá trình xử lý hủy đơn hàng....</b>');
                                            },
                                            success: function (result) {
                                                if (result.st === 1) {
                                                    window.parent.location = window.parent.location;
                                                } else if (result.st === -1) {
                                                    bootbox.alert('<b>' + result.ms + '</b>');
                                                }
                                            }
                                        });
                                        return false;
                                    } else {
                                        bootbox.alert('<b>Vui lòng nhập lý do hủy đơn hàng!</b>');
                                        return false;
                                    }

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
                    return false;
                } else if (_Value == 5) {
                    bootbox.dialog({
                        message: '<textarea name="comadvisoryother" id="comadvisoryother" class="form-control" value="" placeholder="Nhập lý do"></textarea>',
                        title: "Lý do khách trả hàng !",
                        buttons: {
                            success: {
                                label: "Xác nhận",
                                className: "btn-success",
                                callback: function () {
                                    if ($.trim($('#comadvisoryother').val()) != '') {
                                        $.ajax({
                                            type: "POST",
                                            url: baseurl + '/backend/order/update',
                                            cache: false,
                                            data: {
                                                'orderID': _OrderID,
                                                'ispayment': _Value,
                                                'comment_content': $.trim($('#comadvisoryother').val())
                                            },
                                            success: function (result) {
                                                result = JSON.parse(result);
                                                if (result.st == 1) {
                                                    window.parent.location = window.parent.location;
                                                } else {
                                                    bootbox.alert('<b>' + result.ms + '</b>');
                                                    return false;
                                                }
                                            }
                                        });
                                    } else {
                                        bootbox.alert('<p style="text-align:center"><b>Nhập lý do khách trả hàng!</b></p>');
                                        return false;
                                    }
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
                    return false;
                } else {
                    $.ajax({
                        type: "POST",
                        url: baseurl + '/backend/order/update',
                        cache: false,
                        data: {
                            'orderID': _OrderID,
                            'ispayment': _Value,
                        },
                        success: function (result) {
                            result = JSON.parse(result);
                            if (result.st == 1) {
                                window.parent.location = window.parent.location;
                            } else {
                                bootbox.alert('<b>' + result.ms + '</b>');
                            }
                        }
                    });
                }
            });
            $('.changeInfoCus').click(function () {
                var _OrderID = parseInt($('.order-id').text());
                if (_OrderID) {
                    $.ajax({
                        type: "POST",
                        url: baseurl + '/backend/order/get-detail',
                        cache: false,
                        data: {
                            'orderID': _OrderID,
                        },
                        success: function (result) {
                            result = JSON.parse(JSON.parse(result).detailOrder.orde_detail);
                            resultNote = typeof result.note !== 'undefined' ? result.note : '';
                            infoCus = '<table class="info-cus" align="center">\n\
                                            <tr width="30%"><td>Họ tên</td><td><input tyle="text" class="per-100 input-fullname form-control medium" value="' + result.fullname + '"></td></tr>\n\
                                            <tr><td>Địa chỉ</td><td><input tyle="text" class="per-100 input-address form-control medium" value="' + result.address + '"></td></tr>\n\
                                            <tr><td>Số điện thoại</td><td><input tyle="text" class="per-100 input-phone form-control medium" maxLength="11" value="' + result.phone + '"></td></tr>\n\
                                            <tr><td>Email </td><td><input tyle="text" class="per-100 input-email form-control medium" value="' + result.email + '"></td></tr>\n\
                                            <tr><td>Ghi chú của khách </td><td><input tyle="text"  class="per-100 input-note form-control medium" value="' + resultNote + '"></td></tr>\n\
                                        <tr><td>Lý do sửa </td><td><input tyle="text" class="per-100 input-strNote form-control medium" value=""></td></tr>\n\
                                    </table>';

                            bootbox.dialog({
                                message: infoCus,
                                title: "Nhập thông tin khách hàng !",
                                buttons: {
                                    success: {
                                        label: "Xác nhận",
                                        className: "btn-agreeChange",
                                        callback: function () {
                                            fullname = $.trim($('.info-cus').find('.input-fullname').val());
                                            address = $.trim($('.info-cus').find('.input-address').val());
                                            phone = $.trim($('.info-cus').find('.input-phone').val());
                                            email = $.trim($('.info-cus').find('.input-email').val());
                                            note = $.trim($('.info-cus').find('.input-note').val());
                                            strNote = $.trim($('.info-cus').find('.input-strNote').val());
                                            filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                                            if (strNote == '') {
                                                bootbox.alert('Vui lòng nhập lý do sửa thông tin khách hàng!');
                                                return false;
                                            }
                                            if (strNote != '' && fullname != '' && address != '' && phone != '' && email != '' && filter.test(email)) {
                                                $.ajax({
                                                    type: "POST",
                                                    url: baseurl + '/backend/order/update',
                                                    cache: false,
                                                    data: {
                                                        'orderID': _OrderID,
                                                        'fullname': fullname,
                                                        'address': address,
                                                        'phone': phone,
                                                        'email': email,
                                                        'note': note,
                                                        'strNote': strNote
                                                    },
                                                    success: function (result) {
                                                        result = $.parseJSON(result);
                                                        if (result.st == 1) {
                                                            window.location = window.location.href;
                                                        } else {
                                                            bootbox.alert(result.ms);
                                                        }
                                                    }
                                                });
                                            } else {
                                                bootbox.alert('Vui lòng nhập đầy đủ thông tin!');
                                                return false;
                                            }
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
                }
            });

            $('.changeShingFee').click(function () {
                var _OrderID = parseInt($('.order-id').text());
                if (_OrderID) {
                    messageShing = '<div class="row"><div class="col-xs-offset-2 col-xs-3"><div style="padding-top:6px;"><strong>Phí Vận Chuyển:</strong></div></div><div class="col-xs-4"><input type="text " value="' + $(".int-shiping-fee").val() + '" class="form-control input-fee-shiping text-right">&nbsp;</div></div>';
                    bootbox.dialog({
                        message: messageShing,
                        title: "Cập nhật phí vận chuyển",
                        buttons: {
                            success: {
                                label: "Xác nhận",
                                className: "btn-agreeChange",
                                callback: function () {
                                    feeShiping = $.trim($('.input-fee-shiping').val());
                                    $.ajax({
                                        type: "POST",
                                        url: baseurl + '/backend/order/updateShipingFee',
                                        cache: false,
                                        data: {
                                            'orderID': _OrderID,
                                            'feeShiping': feeShiping
                                        },
                                        success: function (result) {
                                            result = $.parseJSON(result);
                                            if (result.st == 1) {
                                                window.location = window.location.href;
                                            } else {
                                                bootbox.alert(result.ms);
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
        $('.btn-addproduct').click(function () {
            bootbox.dialog({
                message: ' <div class="row">\n\
                            <div class="col-xs-6">\n\
                                <div class="row">\n\
                                    <div class="col-xs-5"><div style="padding-top:6px;"><strong>Mã sản phẩm:</strong></div></div>\n\
                                    <div class="col-xs-7"><input type="text" class="form-control input-msp">&nbsp;</div>\n\
                                </div>\n\
                            </div>\n\
                            <div class="col-xs-6">\n\
                                <div class="row">\n\
                                    <div class="col-xs-5"><div style="padding-top:6px;"><strong>Số lượng:</strong></div></div>\n\
                                    <div class="col-xs-7"><input type="text" maxLength="3" class="form-control input-quantity" value="1"></div>\n\
                                </div>\n\
                            </div>\n\
                            </div>',
                title: "Nhập mã sản phẩm quà tặng !",
                buttons: {
                    success: {
                        label: "Xác nhận",
                        className: "btn-success",
                        callback: function () {
                            if ($.trim($('.input-msp').val()) != '' && $.trim($('.input-quantity').val()) != '') {
                                $.ajax({
                                    type: "POST",
                                    url: baseurl + '/backend/order/add-Product-Order',
                                    cache: false,
                                    data: {
                                        'orderID': $('#OrderID').val(),
                                        'proID': $.trim($('.input-msp').val()),
                                        'quantity': $.trim($('.input-quantity').val()),
                                    },
                                    success: function (result) {
                                        result = $.parseJSON(result);
                                        if (result.st == 1) {
                                            bootbox.alert("Thêm sản phẩm thành công!", function () {
                                                window.location = window.location.href;
                                            });
                                        } else {
                                            bootbox.alert('<b> ' + result.ms + ' </b>');
                                            return false;
                                        }
                                    }
                                });
                                return false;
                            } else {
                                bootbox.alert('<b>Vui lòng nhập đầy đủ thông tin Mã sản phẩm và Số lượng!</b>');
                                return false;
                            }
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
        });
        $('.btn-createOrder').click(function () {
            var intOrderID = parseInt($('.order-id').text());
            $.ajax({
                type: "POST",
                url: baseurl + '/backend/order/createorder',
                cache: false,
                dataType: 'json',
                data: {
                    'orderID': intOrderID,
                },
                success: function (result) {
                    if (result.st == 0) {
                        bootbox.alert('<b>' + result.ms + '</b>');
                    } else {
                        bootbox.alert('<b>Tạo đơn hàng thành công!</b>', function () {
                            window.location = result.URL;
                        });
                    }
                }
            });
        });
        $(document).on('keydown', '.input-quantity', function () {
            if (event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 ||
                    (event.keyCode == 65 && event.ctrlKey === true) ||
                    (event.keyCode >= 35 && event.keyCode <= 39)) {
                return;
            }
            else {
                if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105))
                {
                    event.preventDefault();
                }
            }
        });
    },
    edit: function () {
        $(document).ready(function () {
            $(".cf .item").each(function (i, tag) {
                $(tag).find('.edit').click(function () {
                    product_order_id = $(this).attr('rel');
                    product_id = $(this).attr('id');
                    currentPrice = ($(tag).find('.prodPrice').text());
                    currentDiscount = parseInt($(tag).find('.disCount').text());
                    currentQuantity = parseInt($(tag).find('.prodQuantity').text());
                    totalPrice = ($(tag).find('.TotalPrice').text());
                    productName = ($(tag).find('.ProductName').text());
                    bootbox.dialog({
                        message: '<div class="row">\n\
                                    <div class="col-lg-12">\n\
                                        <center><b style="color:blue">' + productName + '</b></center>\n\
                                    </div><br/>\n\
                                   </div> \n\
                                   <div class="row">\n\
                                    <div class="col-xs-3">\n\
                                        <center><b>Giá (VNĐ)</b></center>\n\
                                        <input class="form-control curent_price" value="' + currentPrice + '"/>\n\
                                    </div>\n\
                                    <div class="col-xs-3">\n\
                                        <center><b>Số lượng</b></center>\n\
                                        <input class="form-control curent_quantity" value="' + currentQuantity + '" />\n\
                                    </div>\n\
                                    <div class="col-xs-3">\n\
                                        <center><b>CKhấu (%)</b></center>\n\
                                        <input class="form-control curent_discount" value="' + currentDiscount + '" />\n\
                                    </div>\n\
                                    <div class="col-xs-3">\n\
                                        <center><b>Tổng tiền (VNĐ)</b></center>\n\
                                        <input class="form-control curent_totalprice" value="' + totalPrice + '" readonly />\n\
                                    </div>\n\
                                </div><br/>\n\
                                <input type="hidden" value="">\n\
                                <textarea name="commContent" id="commContent" class="form-control" value="" placeholder="Nhập lý do chỉnh sửa"></textarea>\n\
                                <center><p id="mserror" style="color:red;display:none"><b>Chưa nhập lý do sao xác nhận được cha nội ???</b></p></center>',
                        title: "Chỉnh sửa sản phẩm",
                        buttons: {
                            success: {
                                label: "Cập nhật",
                                className: "btn-success",
                                callback: function () {
                                    if ($("#commContent").val() == '') {
                                        $('#mserror').css('display', '');
                                        return false;
                                    }
                                    $.ajax({
                                        type: "POST",
                                        url: baseurl + '/backend/order/update-product',
                                        cache: false,
                                        dataType: 'json',
                                        data: {
                                            'newPrice': newPrice,
                                            'newDiscount': newDiscount,
                                            'newQuantity': newQuantity,
                                            'newTotalPrice': newTotalPrice,
                                            'orderID': orderID,
                                            'order_note': $("#commContent").val(),
                                            'product_order_id': product_order_id,
                                            'product_id': product_id,
                                        },
                                        success: function (result) {
                                            if (result.st == 1) {
                                                bootbox.alert(result.ms, function () {
                                                    window.location = window.location.href;
                                                });
                                            } else {
                                                bootbox.alert(result.ms, function () {
                                                    return false;
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
                    })
                    Order.reset();
                })

            })
        })
    },
    del: function () {
        $(document).ready(function () {
            $(".cf .item").each(function (i, tag) {
                $(tag).find('.denied').click(function () {
                    product_order_id = $(this).attr('rel');
                    if (orderID) {
                        bootbox.dialog({
                            message: 'Bạn có muốn xóa sản phẩm này khỏi đơn hàng không ???<br><br><textarea value="" aria-controls="editable-sample" class="per-100 input-seaching form-control medium reason-del" placeholder="Nhập lý do xóa sản phẩm"></textarea>',
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
                                        var strReason = $.trim($('.reason-del').val());
                                        if (strReason != '') {
                                            $.ajax({
                                                type: "POST",
                                                url: baseurl + '/backend/order/remove-product',
                                                cache: false,
                                                dataType: 'json',
                                                data: {
                                                    'orderID': orderID,
                                                    'product_order_id': product_order_id,
                                                    'orderNote': strReason,
                                                },
                                                success: function (result) {
                                                    if (result.st == 1) {
                                                        bootbox.alert(result.ms, function () {
                                                            window.location = window.location.href;
                                                        });
                                                    } else {
                                                        bootbox.alert(result.ms, function () {
                                                            window.location = window.location.href;
                                                        });
                                                    }
                                                }
                                            });
                                        } else {
                                            bootbox.alert('Nhập lý do xóa sản phẩm!');
                                            return false;
                                        }
                                    }
                                }
                            }
                        });
                    }
                })

            });
        });
    },
    reset: function () {
        $('.curent_price').number(true, 0);
        $('.curent_totalprice').number(true, 0);
        $('.curent_quantity').number(true);
//        $('.curent_discount').number( true);
        newPrice = $('.curent_price').val();
        newDiscount = $('.curent_discount').val();
        newQuantity = $('.curent_quantity').val();
        newTotalPrice = $('.curent_totalprice').val();

        $('.curent_price, .curent_discount, .curent_quantity').on('keyup', function () {
            newPrice = $('.curent_price').val();
            newDiscount = $('.curent_discount').val();
            newQuantity = $('.curent_quantity').val();
            if (newDiscount == 0) {
                newTotalPrice = newPrice * newQuantity;
            } else {
                newTotalPrice = (newPrice * newQuantity) - (newPrice * newQuantity * newDiscount) / 100;
            }
            $('.curent_totalprice').val(newTotalPrice);
        })
    },
    addCommas: function (nStr)
    {
        nStr += '';
        x = nStr.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
        }
        return x1 + x2;
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
    delOrder: function () {
        $(document).ready(function () {
            $(".cf .trash").on('click', function () {
                orderID = $(this).attr('rel');
                +
                        console.log(orderID);
                if (orderID) {
                    bootbox.confirm('Bạn có muốn xóa đơn hàng không ???', function (result) {
                        if (result) {
                            $.ajax({
                                type: "POST",
                                url: baseurl + '/backend/order/del-order',
                                cache: false,
                                dataType: 'json',
                                data: {
                                    'orderID': orderID,
                                },
                                success: function (result) {
                                    if (result.st == 1) {
                                        bootbox.alert(result.ms, function () {
                                            window.location = window.location.href;
                                        });
                                    } else {
                                        bootbox.alert(result.ms, function () {
                                            window.location = window.location.href;
                                        });
                                    }
                                }
                            });
                        }
                    })
                }
                console.log(orderID);
//                alert('Sai tao đập!');
//                $(tag).find('.denied').click(function () {
//                    product_order_id = $(this).attr('rel');
//                    if (orderID) {
//                        bootbox.confirm('Bạn có muốn xóa sản phẩm này khỏi đơn hàng không ???', function (result) {
//                            if (result) {
//                                $.ajax({
//                                    type: "POST",
//                                    url: baseurl + '/backend/order/remove-product',
//                                    cache: false,
//                                    dataType: 'json',
//                                    data: {
//                                        'orderID': orderID,
//                                        'product_order_id': product_order_id
//                                    },
//                                    success: function (result) {
//                                        if (result.st == 1) {
//                                            bootbox.alert(result.ms, function () {
//                                                window.location = window.location.href;
//                                            });
//                                        } else {
//                                            bootbox.alert(result.ms, function () {
//                                                window.location = window.location.href;
//                                            });
//                                        }
//                                    }
//                                });
//                            }
//                        });
//                    }
//                })

            });
        });
    },
    product: function () {
        $('.changeStatusProdOrder').change(function () {
            var status = $(this).val();
            var id = $(this).data('id');
            console.log(status, id);
            $.ajax({
                type: "POST",
                url: baseurl + '/backend/order/edit-status-prod-order',
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
        Order.date();
    }

};
