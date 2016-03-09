var Ward = {
    index: function() {
        $(document).ready(function() {

            Ward.add();
            Ward.edit();
            Ward.del();
            $('select#city_id').change(function() {
                str = '<tr><td colspan="5" style="text-align: center;">Chọn Tỉnh / Thành và Quận / Huyện cần xem</td></tr>';
                $('#ward-container').html(str);
                var city_id = parseInt($(this).val());
                if (city_id > 0) {
                    $.ajax({
                        type: "POST",
                        url: baseurl + '/backend/district/getDistrict',
                        dataType: 'json',
                        data: {'city_id': city_id},
                        success: function(result) {
//                            result = jQuery.parseJSON(result);
                            if (result.data) {
                                $('select#district_id').html('<option value="">===== Chọn Quận / Huyện =====</option>')
                                $.each(result.data, function(k, v) {
                                    $('select#district_id').append('<option value="' + v.dist_id + '">' + v.dist_name + '</option>');
                                });
                            }
                        }
                    });
                } else {
                    $('select#district_id').html('<option value="0">===== Chọn Quận / Huyện =====</option>');
                }
            });
            $('select#district_id').change(function(event, page) {

                var district_id = parseInt($(this).val());
                if (page === 'undefined') {
                    page = 1;
                }
                if (district_id > 0) {
                    $.ajax({
                        type: "POST",
                        url: baseurl + '/backend/ward/get-ward',
                        dataType: 'json',
                        data: {
                            'district_id': district_id,
                            'page': page
                        },
                        beforeSend: function() {
                            $('#ward-container').css('opacity', 0.4);
                        },
                        success: function(result) {
                            $('#ward-container').css('opacity', 1);
                            var str = new String();
                            if (result.error === 0) {
                                for (var i = 0; i < result.data.length; i++) {
                                    var item = result.data[i];
                                    str += '<tr>';
                                    str += '<td>' + item.ward_id + '</td>';
                                    str += '<td class="wardName">' + item.ward_name + '</td>';
                                    str += '<td class="ordering">' + item.ward_ordering + '</td>';
                                    str += '<td class="focus">' + item.ward_is_focus + '</td>';
                                    str += '<td style="text-align: center;">';
                                    str += '<i class="fa fa-pencil action edit" style="font-size: 2em;" wardid="' + item.ward_id + '" data-original-title="Sửa Phường / Xã" data-placement="top"></i> &nbsp;';
                                    str += '<i class="fa fa-times-circle  action delete" style="font-size: 2em;" wardid="' + item.ward_id + '" data-original-title="Xoas Phường / Xã" data-placement="top"></i>';
                                    str += '</a>';
                                    str += '</td>';
                                    str += '</tr>';
                                }

                                if (result.total > 0) {
                                    var rowPerPage = 15;
                                    var record = 3;
                                    var total = result.total;
                                    if (total % rowPerPage === 0) {
                                        totalPage = total / rowPerPage;
                                    } else {
                                        totalPage = Math.floor(total / rowPerPage) + 1;
                                    }

                                    //      $("#paginator").html(Pagination(record, totalPage, page));
                                    $("ul#paging li a").click(function() {
                                        currentNow = $(this).attr('name').substr(4);
                                        if ($(this).attr('name').length > 0) {
                                            $('select#district_id').trigger('change', [currentNow]);
                                        }
                                    });
                                }
                            } else {
                                str = '<tr><td colspan="5" style="text-align: center;">Chọn Tỉnh / Thành và Quận / Huyện cần xem</td></tr>';
                            }

                            $('#ward-container').html(str);
                        }
                    });
                } else {
                    str = '<tr><td colspan="5" style="text-align: center;">Chọn Tỉnh / Thành và Quận / Huyện cần xem</td></tr>';
                    $('#ward-container').html(str);
                }

            });
        }
        );
    },
    add: function() {
        $('#frmAddWard').click(function() {
            frm = $("#frm");
            frm.validate({
                rules: {
                    wardName: {required: true, maxlength: 255, minlength: 5},
                    district_id: {required: true, min: 1},
                    city_id: {required: true, min: 1},
                    ordering: {number: true}
                },
                messages: {
                    wardName: "Tên Phường/Xã không được để trống. Tối đa 255 kí tự, và phải trên 5 kí tự",
                    ordering: 'Thứ tự phải là chữ số',
                    city_id: 'Tên Tỉnh / Thành không được bỏ trống',
                    district_id: 'Tên Quận / Huyện không được bỏ trống'
                }
            });
        });
        $(document).ready(function() {
            $('#done').click(function() {
                $('#frm').submit();
                if ($("#frm").valid()) {
                    $('#frm').submit();
                }
            });
        });
    },
    edit: function() {
        $('#ward-container').on('click', '.edit', function() {
            var wardID = $(this).attr('wardid'),
                    isFocus = $.trim($(this).parent('td').prev().text()),
                    ordering = $.trim($(this).parent('td').prev().prev().text()),
                    wardName = $.trim($(this).parent('td').prev().prev().prev().text());
            if (!wardID || !isFocus || !ordering || !wardName) {
                bootbox.alert('<b>Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại</b>', function() {
                    window.location = window.location.href;
                });
            }
            $('.alert-success, .alert-danger').hide();
            $('#cancel').attr('id', 'cancelEdit');
            $('#frm').attr('action', baseurl + '/backend/ward/edit/id/' + wardID);
            $("input[name='wardName']").val(wardName);
            $("input[name='ordering']").val(ordering);
            $("input[name='isFocus']").val(isFocus);
            var destination = $('#frmAddWard').offset().top - 62;
            $("html:not(:animated),body:not(:animated)").animate({
                scrollTop: destination
            }, 300);
            $('#cancelEdit').click(function() {
                clearForm($('#frm'));
                validator.resetForm();
                $(this).attr('id', 'cancel');
                $('#frm').removeAttr('action');
            });
        });

//        $('#done').click(function() {
//            var formData = frm.serialize();
//            formData += '&wardID=' + wardID;
//            $.ajax({
//                type: "POST",
//                url: baseurl + '/backend/ward/edit',
//                data: formData,
//                dataType: 'json',
//                beforeSend: function() {
//
//                },
//                success: function(result) {
//                    $('.alert').hide();
//                    if (result.error === 1) {
//                        $('p.error-msg').html('- ' + result.msg);
//                        $('.alert-error').fadeIn();
//                    } else {
//                        $('strong.success-msg').html(result.msg);
//                        $('.alert-success').fadeIn();
//                        clearForm('#frm');
//                        $('.cancel').trigger('click');
//                        $('select#district_id').trigger('change');
//                    }
//                }
//            });
//        });
    },
    del: function() {
        $(document).on('click', '.delete', function() {
            //wardID = $(this).attr('wardid');
            //alert('asdasdasdsa');
            if (wardID) {
                var $this = $(this);
                bootbox.confirm('Bạn có muốn xóa Quận / Huyện này không', function(res) {
                    if (res) {
                        $.ajax({
                            type: "POST",
                            url: baseurl + '/backend/ward/delete',
                            cache: false,
                            dataType: 'json',
                            data: {
                                'wardID': wardID
                            },
                            success: function(result) {
                                console.log(result);
                                if (result.success === 1) {
                                    bootbox.alert('<b>' + result.message + '</b>', function() {
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
    }
};
