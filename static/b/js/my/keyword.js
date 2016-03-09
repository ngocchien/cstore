var Keyword = {
    index: function () {
        $(document).ready(function () {
            Keyword.del();
            Keyword.del_all();
            Keyword.submitClose();
            Keyword.jump_position();
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
    del: function () {
        $(document).ready(function () {
            $('.remove-keyword').click(function () {

                var word_id = $(this).data('id');
                if (!word_id) {
                    bootbox.alert('<b>Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại</b>', function () {
                        window.location = window.location.href;
                    });
                }

                bootbox.confirm('<b>Bạn có muốn xóa keyword này không ???</b>', function (result) {
                    if (result) {
                        $.ajax({
                            type: "POST",
                            url: baseurl + '/backend/keyword/delete',
                            cache: false,
                            dataType: 'json',
                            data: {'word_id': word_id},
                            success: function (result) {
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
            });
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
                    bootbox.alert('<b>Vui lòng chọn ít nhất một keyword để xóa</b>', function () {
                    });
                    return;
                }
                bootbox.confirm('<b>Bạn có muốn xóa những keyword này không ???</b>', function (result) {
                    if (result) {
                        $.ajax({
                            type: "POST",
                            url: baseurl + '/backend/keyword/delete',
                            cache: false,
                            dataType: 'json',
                            data: {'word_id': id},
                            success: function (result) {
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
            });
        });
    },
    jump_position: function () {
        $('.btn-crawler').click(function () {
            var pos = $('input[name="position"]').val();
            $.ajax({
                type: "POST",
                url: baseurl + '/backend/keyword/index',
                cache: false,
                dataType: 'json',
                data: {'position': pos},
                success: function (result) {
                    if (result.success === 1) {
                        $('.edit-info').html('<h5 class="color-success">Thay đổi vị trí crawler thành công</h5>').hide().fadeIn('slow', function () {
                                    $('.edit-info').fadeOut(1000);
                                });
                    } else if (result.error === 1) {
                        $('.edit-info').html('<h5 class="color-fail">Thay đổi thất bại</h5>').hide().fadeIn('slow', function () {
                                    $('.edit-info').fadeOut(1000);
                                });
                    }
                }
            });
        });
    }
}


