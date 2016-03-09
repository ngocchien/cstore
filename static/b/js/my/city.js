var City = {
    index: function() {
        $(document).ready(function() {
            validator = $('#frm').validate({
                rules: {
                    cityName: {
                        required: true,
                        minlength: 3,
                        maxlength: 255
                    },
                    ordering: {number: true},
                    isFocus: {number: true}
                },
                messages: {
                    cityName: {
                        required: 'Tên Tỉnh/Thành không được để trống.',
                        minlength: 'Tên Tỉnh/Thành tối thiểu từ 3 kí tự trở lên.',
                        maxlength: 'Tên Tỉnh/Thành tối đa 255 kí tự'
                    },
                    ordering: 'Thứ tự phải là chữ số',
                    isFocus: 'Độ ưu tiên phải là chữ số'
                }
            });
            $('#cancel').click(function() {
                validator.resetForm();
                clearForm($('#frm'));
            });
            City.add();
            City.edit();
            City.del();
        });
    },
    add: function() {
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
        $(document).ready(function() {
            $('.edit').click(function() {
                validator.resetForm();
                
                var _parent = $(this).parent().parent();
                var cityID = $(this).data('id'),
                        areaID = $.trim(_parent.find('.areaID').text()),
                        isFocus = $.trim(_parent.find('.isFocus').text()),
                        ordering = $.trim(_parent.find('.ordering').text()),
                        cityName = $.trim(_parent.find('.cityName').text());
                if (!cityID || !isFocus || !ordering || !cityName || !areaID) {
                    bootbox.alert('<b>Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại</b>', function() {
                        window.location = window.location.href;
                    });
                }
                $('#frmTitle').text('Chỉnh sửa Tỉnh / Thành');
                $('.alert-success, .alert-danger').hide();
                $('#cancel').attr('id', 'cancelEdit');
                $('#frm').attr('action', baseurl + '/backend/city/edit/id/' + cityID);
                
                $("input[name='cityName']").val(cityName);
                $("input[name='ordering']").val(ordering);
                $("input[name='isFocus']").val(isFocus);
                $("select[name='areaID']").val(areaID);
                
                var destination = $('#frmAddCity').offset().top - 62;
                $("html:not(:animated),body:not(:animated)").animate({
                    scrollTop: destination
                }, 300);
                $('#cancelEdit').click(function() {
                    clearForm($('#frm'));
                    validator.resetForm();
                    $(this).attr('id', 'cancel');
                    $('#frm').removeAttr('action');
                    $('#frmTitle').text('Thêm Tỉnh / Thành');
                });
            });
        });
    },
    del: function() {
        $(document).ready(function() {
            $('.delete').click(function() {
                var cityID = $(this).data('id');
                if (cityID) {
                    bootbox.confirm('<b>Bạn có muốn xóa Tỉnh / Thành này không ???</b>', function(result) {
                        if (result) {
                            $.ajax({
                                type: "POST",
                                url: baseurl + '/backend/city/delete',
                                cache: false,
                                dataType: 'json',
                                data: {'cityID': cityID},
                                success: function(result) {
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
        });
    }
};
