var User = {
    index: function () {
        $(document).ready(function () {
            $('body').on('click', '.delete', function () {
                $(this).parents('li').remove();
            });
            $('.view-orders').click(function(){
               userID = parseInt($(this).parents("tr").find("> td").eq(0).text());
               //alert(userID);return false;
               $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/order/get-list',
                    cache: false,
                    data: {
                        'userID': userID,
                    },
                    /*beforeSend: function() {
                        
                    },*/
                    success: function (result) {
                        result = JSON.parse(result);
                        if(result.st == 1){
                        _listOrders =   '<div class="text-center">\n\
                                            <h4>Danh sách đơn hàng cho user: ' + userID + '</h4>\n\
                                        </div>\n\
                                        <table class="info-cus table-bordered" align="center">\n\
                                            <tr>\n\
                                                <td class="text-center" width="100"><b>Mã hóa đơn</b></td>\n\
                                                <td class="text-center" width="100"><b>Trạng thái</b></td>\n\
                                                <td class="text-center" width="100"><b>Ngày tạo</b></td>\n\
                                                <td class="text-center" width="200"><b>Tổng tiền</b></td>\n\
                                            </tr>';
                            $.each(result.listOrders,function(i,val){
                               _listOrders += ' <tr><td class="text-center">' + val.orde_id + '</td>\n\
                                                    <td class="text-center">' + getStringStatus(val.is_payment) + '</td>\n\
                                                    <td class="text-center">' + getFormattedDate(val.orde_created) + '</td>\n\
                                                    <td class="text-center">' + formatNumber(val.orde_total_price) + '</td>\n\
                                                </tr>';
                            });
                            _listOrders += '</table>';
                            bootbox.alert(_listOrders);
                        }else{
                            bootbox.alert(result.ms);
                        }
                    }
                });
            });
            city = $('#city');
            cityID = city.val();
            cityName = city.find(':selected').text();

            district = $('#district');
            districtID = district.val();
            districtName = district.find(':selected').text();

            $("#frm").validate({
                ignore: '.ignore',
                rules: {
                    fullName: {required: true, minlength: 3, maxlength: 255},
                    email: {required: true, minlength: 3, maxlength: 255, email: true},
                    address: {required: true, minlength: 5, maxlength: 255},
                    mobileNumber: {required: true, maxlength: 11},
                    password: {required: true, maxlength: 255, minlength: 3},
                    rePassword: {required: true, maxlength: 255, minlength: 3, equalTo: "#password"}
                }, messages: {
                    fullName: {
                        required: 'Xin vui lòng nhập Họ và Tên.',
                        minlength: 'Họ và tên tối thiểu từ 3 kí tự trở lên.',
                        maxlength: 'Họ và tên tối đa 255 kí tự'
                    },
                    email: {
                        required: 'Xin vui lòng nhập Email',
                        email: 'Xin vui lòng nhập đúng định dạng email. Ví dụ : hotro@amazon247.vn',
                        minlength: 'Email tối thiểu từ 3 kí tự trở lên.',
                        maxlength: 'Email tối đa 255 kí tự'
                    },
                    address: {
                        required: 'Xin vui lòng nhập địa chỉ',
                        minlength: 'Địa chỉ tối thiểu từ 5 kí tự trở lên.',
                        maxlength: 'Địa chỉ tối đa 255 kí tự'
                    },
                    mobileNumber: 'Số di động không được để trống. Tối đa 11 chữ số',
                    password: 'Mật khẩu không được để trống. Tối thiểu trên 3 kí tự và tối đa 255 kí tự',
                    rePassword: 'Vui lòng nhập lại mật khẩu.',
                    city: 'Vui lòng chọn Tỉnh/Thành',
                    district: 'Vui lòng chọn Quận/Huyện'
                }
            });
            jQuery.extend(jQuery.validator.messages, {
                equalTo: "Password không trùng khớp. Xin vui lòng kiểm tra lại"
            });
            $.validator.addMethod("dateFormatValidate", function (value, element) {
                return value.match(/^\d\d?\-\d\d?\-\d\d\d\d$/);
            }, "Vui lòng nhập ngày tháng theo đúng định dạng : dd-mm-yyyy.");

            $.validator.addMethod("validateAddress", function (value, element) {
                return validateAddress(value);
            }, "Vui lòng nhập số nhà và tên đường đúng định dạng. Ví dụ: 1 Lê Duẩn");

            $.validator.addMethod("greaterThanZero", function (value, element) {
                return this.optional(element) || (parseFloat(value) > 0);
            }, "* Amount must be greater than zero");

            $("#mobileNumber").inputmask({"mask": "9999999999[9]"});
            $('#birthdate').inputmask({"mask": "99-99-9999"});     

            User.add();
            User.del();
            User.submitClose();
            User.getSelectGroup();
        });
    }, submitClose: function () {
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
    add: function () {
        city.change(function () {
            cityID = $(this).val();
            cityName = $(this).find(':selected').text();
            if (cityID) {
                $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/district/get-list',
                    cache: false,
                    dataType: 'json',
                    data: {
                        cityID: cityID
                    },
                    success: function (result) {
//                        console.log(result); return false;
                        if (result) {
                            district.html('<option value="">-- Chọn Quận / Huyện --</option>');
                            $.each(result, function (key, value) {
                                district.append('<option value="' + value.dist_id + '">' + value.dist_name + '</option>');
                            });
                        } else {
                            bootbox.alert('Không thể lấy dữ liệu Quận / Huyện');
                        }
                    }
                });
            }
        });

    },
    edit: function () {
        $(document).ready(function () {
            $('#city, #district').change(function () {

            });
        });
    },
    del: function () {
        $(document).ready(function () {
            $('.remove').click(function () {
                userID = $(this).attr('rel');
                if (userID) {
                    bootbox.confirm('Bạn có muốn xóa người dùng này không', function (result) {
                        if (result) {
                            $.ajax({
                                type: "POST",
                                url: baseurl + '/backend/user/delete',
                                cache: false,
                                dataType: 'json',
                                data: {
                                    'user_id': userID
                                },
                                success: function (result) {
                                    if (result) {
                                        window.location = window.location;
                                    } else {
                                        bootbox.alert('Không thể xóa người dùng này');
                                    }
                                }
                            });
                        }
                    });
                }
            });

            //filter
            $('#btnToggleFilterUser').click(function () {
                $('div#frmFilterUser').slideToggle();
            });
        });
    },
    getSelectGroup: function(){
        var selectGroup = true;
        $('#group_id').change(function () {
            var arrGroup = $(this).val();
            if ($.inArray("-1", arrGroup) > -1) {
                $(this).selectpicker('selectAll');
                selectGroup = false;
            } else {
                if (selectGroup == false) {
                    $(this).selectpicker('deselectAll');
                    selectGroup = true;
                }
            }
            var Group = '';
            arrGroup = ($(this).val());
            if (jQuery.isEmptyObject(arrGroup) == false) {
                var i = 0;
                $.each(arrGroup, function (key, value) {
                    Group = Group + value;
                    i += 1;
                    if (arrGroup.length != i)
                        Group = Group + ',';
                });
            }
            $('#group').val(Group);
        });
    }
}
function clearForm(ele) {
    $(ele).find(':input').each(function () {
        switch (this.type) {
            case 'password':
            case 'select-multiple':
            case 'select-one':
            case 'text':
            case 'textarea':
                $(this).val('');
                break;
            case 'checkbox':
            case 'radio':
                this.checked = false;
        }
    });
}

//validate only home number add street address
function validateAddress(strAddress) {
    if (!/^[/a-z0-9A-ZÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂẾưăạảấầẩẫậắằẳẵặẹẻẽềềếểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễệỉịọỏốồổỗộớờởỡợụủứừỬỮỰỲỴÝỶỸửữựỳỵỷỹ ]+$/i.test(strAddress)) {
        return false;
    }
    if (/Phường/.test(strAddress) || /phường/.test(strAddress) || /phuong/.test(strAddress) || /Quận/.test(strAddress) || /quận/.test(strAddress) || /quan/.test(strAddress) || /Đường/.test(strAddress) || /đường/.test(strAddress) || /duong/.test(strAddress)) {
        return false;
    }
    var arrAddress = strAddress.split(' ');
    if (arrAddress.length <= 1) {
        return false;
    }
    if ($.isArray(arrAddress)) {
        if (!/^[a-z0-9A-Z/]+$/i.test(arrAddress[0])) {
            return false;
        }

        if (!/^[a-zA-ZÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂẾưăạảấầẩẫậắằẳẵặẹẻẽềềếểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễệỉịọỏốồổỗộớờởỡợụủứừỬỮỰỲỴÝỶỸửữựỳỵỷỹ]+$/i.test(arrAddress[1]) || arrAddress[1].length <= 1) {
            return false;
        }
        if (!/^[a-z0-9A-ZÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂẾưăạảấầẩẫậắằẳẵặẹẻẽềềếểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễệỉịọỏốồổỗộớờởỡợụủứừỬỮỰỲỴÝỶỸửữựỳỵỷỹ]+$/i.test(arrAddress[2])) {
            return false;
        }
    }
    return true;


}
function getFormattedDate(UNIX_timestamp){
  var a = new Date(UNIX_timestamp*1000);
  var months = ['01','02','03','04','05','06','07','08','09','10','11','12'];
  var year = a.getFullYear();
  var month = months[a.getMonth()];
  var date = a.getDate();
  var hour = a.getHours();
  var min = a.getMinutes();
  var sec = a.getSeconds();
  var time = date + '/' + month + '/' + year;
  return time;
}
function formatNumber(nStr)
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
}
function getStringStatus(int){
    switch (parseInt(int)) {
        case -1:
            status = "Đã hủy";
            break;
        case 0:
            status = "Chờ kiểm duyệt";
            break;
        case 1:
            status = "Đã duyệt";
            break;
        case 2:
            status = "Đang giao hàng";
            break;
        case 3:
            status = "Đã nhận hàng";
            break;
        case 4:
            status = "Đã thu tiền";
            break;
    }
    return status;
}