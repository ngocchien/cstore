var District = {
    index: function () {
        $(document).ready(function () {
            validator = $('#frm').validate({
                rules: {
                    districtName: {
                        required: true,
                        minlength: 5,
                        maxlength: 255
                    },
                    ordering: {number: true},
                    isFocus: {number: true}
                },
                messages: {
                    districtName: {
                        required: 'Tên Quận/ Huyện không được để trống.',
                        minlength: 'Tên Quận/ Huyện tối thiểu từ 5 kí tự trở lên.',
                        maxlength: 'Tên Quận/ Huyện tối đa 255 kí tự'
                    },
                    ordering: 'Thứ tự phải là chữ số',
                    isFocus: 'Độ ưu tiên phải là chữ số'
                }
            });
            $('#cancel').click(function () {
                validator.resetForm();
                clearForm($('#frm'));
            });
            District.add();
            District.edit();
            District.del();
        });
        
        $('select#cityId').change(function (event, page) {
            var city_id = parseInt($(this).val());
            if (page == undefined) {
                page = 1;
            }

            if (city_id > 0) {
                $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/district/getDistrict',
                    cache: false,
                    dataType: 'json',
                    data: {
                        'city_id': city_id,
                        'page': page
                    },
                    beforeSend: function () {
                        $('#district-container').css('opacity', 0.4);
                    },
                    success: function (result) {
                        $('#district-container').css('opacity', 1);
                        var str = new String();
                        if (result.error == 0) {
                            console.log(result.data);
                            for (var i = 0; i < result.data.length; i++) {
                                var item = result.data[i];
                                var ruralIcon = item.dist_rural == 1 ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>';
                                str += '<tr>';
                                str += '<td class="text-center">' + item.dist_id + '</td>';
                                str += '<td class="districtName">' + item.dist_name + '</td>';
                                str += '<td class="text-center">' + ruralIcon + '</td>';
                                str += '<td class="ordering">' + item.dist_ordering + '</td>';
                                str += '<td class="isFocus">' + item.dist_is_focus + '</td>';
                                str += '<td class="text-center" width="200">';
                                str += '<a  href="javascript:;"  class="btn btn-primary btn-xs  edit"  data-cityid="' + item.city_id + '" data-isrural="' + item.dist_rural + '" data-id="' + item.dist_id + '" data-original-title="Sửa Quận/ Huyện" data-placement="top"><i class="icon-pencil"></i> Sửa</a> ';
                                str += '<a  href="javascript:;"   class="btn btn-danger btn-xs delete"  data-cityid="' + item.city_id + '" data-isrural="' + item.dist_rural + '" data-id="' + item.dist_id + '" data-original-title="Sửa Quận/ Huyện" data-placement="top"><i class="icon-trash "></i> Xóa</a>';
                                //    str += '<i class="fa fa-pencil action tooltips edit" style="font-size: 2em;" data-cityid="' + item.city_id + '" data-isrural="' + item.dist_rural + '" data-id="' + item.dist_id + '" data-original-title="Sửa Quận/ Huyện" data-placement="top"></i>&nbsp;';
                                //   str += '<i class="fa fa-times-circle tooltips action delete" style="font-size: 2em;" data-id="' + item.dist_id + '" data-original-title="Xóa Quận/ Huyện" data-placement="top"></i>';
                                str += '</td>';
                                str += '</tr>';
                            }

                            if (result.total > 0) {
                                var rowPerPage = 15;
                                var record = 3;
                                var total = result.total;
                                if (total % rowPerPage == 0) {
                                    totalPage = total / rowPerPage;
                                } else {
                                    totalPage = Math.floor(total / rowPerPage) + 1;
                                }

//                                $("#paginator").html(Pagination(record, totalPage, page));

                                $("ul#paging li a").click(function () {
                                    currentNow = $(this).attr('name').substr(4);
                                    if ($(this).attr('name').length > 0) {
                                        $('select#cityId').trigger('change', [currentNow]);
                                    }
                                });
                            }
                        } else {
                            str = '<tr><td colspan="6" style="text-align: center;">Chọn Tỉnh / Thành Phố cần xem Quận / Huyện</td></tr>';
                        }

                        $('#district-container').html(str);
                    }
                });
            } else {
                str = '<tr><td colspan="6" style="text-align: center;">Chọn Tỉnh / Thành Phố cần xem Quận / Huyện</td></tr>';
                $('#district-container').html(str);
            }

        });
    },
    add: function () {
        $(document).ready(function () {
            $('#done').click(function () {
                $('#frm').submit();
                if ($("#frm").valid()) {
                    $('#frm').submit();
                }
            });
        });
    },
    edit: function () {
        $('#district-container').on('click', '.edit', function () {
            validator.resetForm();
            var _parent = $(this).parent().parent();
            var districtID = $(this).data('id'),
                    cityID = $(this).data('cityid'),
                    isRural = $(this).data('isrural'),
                    isFocus = $.trim(_parent.find('.isFocus').text()),
                    ordering = $.trim(_parent.find('.ordering').text()),
                    districtName = $.trim(_parent.find('.districtName').text());
            //alert(districtID);
            if (!districtID || !isFocus || !ordering || !districtName) {
                bootbox.alert('<b>Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại</b>', function () {
                    window.location = window.location.href;
                });
            }
            $('.alert-success, .alert-danger').hide();
            $('#cancel').attr('id', 'cancelEdit');
            $('#frm').attr('action', baseurl + '/backend/district/edit/id/' + districtID);
            $("input[name='districtName']").val(districtName);
            $("select[name='city_id']").val(cityID);
            $("input[name='ordering']").val(ordering);
            $("input[name='isFocus']").val(isFocus);
            isRural == 1 ? $("input[name='isRural']").attr('checked', true) : $("input[name='isRural']").attr('checked', false);
            var destination = $('#frmAddDistrict').offset().top - 62;
            $("html:not(:animated),body:not(:animated)").animate({
                scrollTop: destination
            }, 300);
            $('#cancelEdit').click(function () {
                clearForm($('#frm'));
                validator.resetForm();
                $(this).attr('id', 'cancel');
                $('#frm').removeAttr('action');
            });
        });
//        $('#done1').click(function() {
//            var formData = frm.serialize();
//            formData += '&districtID=' + districtID;
//
//            $.ajax({
//                type: "POST",
//                url: baseurl + '/backend/district/edit',
//                data: formData,
//                dataType: 'json',
//                beforeSend: function() {
//
//                },
//                success: function(result) {
//                    $('.alert').hide();
//
//                    if (result.error == 1) {
//                        $('p.error-msg').html('- ' + result.msg);
//                        $('.alert-error').fadeIn();
//                    } else {
//                        $('strong.success-msg').html(result.msg);
//                        $('.alert-success').fadeIn();
//
//                        clearForm('#frm');
//
//                        $('.cancel').trigger('click');
//                        $('select#city_id').trigger('change');
//                    }
//                }
//            });
//        });
    },
    del: function () {
        $(document).ready(function () {
            $('#district-container').on('click', '.delete', function () {
                var districtID = $(this).data('id');
//                alert(districtID);
                if (districtID) {
                    bootbox.confirm('<b>Bạn có muốn xóa Quận/ Huyện này không ???</b>', function (result) {
                        if (result) {
                            $.ajax({
                                type: "POST",
                                url: baseurl + '/backend/district/delete',
                                cache: false,
                                dataType: 'json',
                                data: {'districtID': districtID},
                                success: function (result) {
                                    console.log(result);
                                    if (result.success === 1) {
                                        bootbox.alert('<b>' + result.message + '</b>', function () {
                                            window.location = window.location.href;
                                        });
                                    } else if (result.error === 1) {
                                        bootbox.alert('<b>' + result.message + '</b>');
                                    }
                                }
                            });
                        }
                    });
                }
            });
        });
    }
};
