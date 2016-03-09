var Banner = {
    del: function () {
        $(document).ready(function () {
            $('.sumo-multiple-select-box').SumoSelect({okCancelInMulti: false});
//            $(".select_row").on('click',function(){
//                var select_info=$(this).find('.select_info');
//                if(select_info.prop('checked')){
//                    select_info.prop('checked',false);
//                }else{
//                    select_info.prop('checked',true);
//                }
//            });
            $('.delete_all').click(function () {
                var id = [];
                var select_in = $('input.select_info:checked');
                select_in.each(function (i, tag) {
                    id[i] = $(tag).val();
                });
                if (id.length < 1) {
                    bootbox.alert('<b>Vui lòng chọn ít nhất một sản phẩm để xóa</b>', function () {
                    });
                    return false;
                }
                bootbox.confirm('<b>Bạn có muốn xóa những Sản phẩm này không ???</b>', function (result) {
                    if (result) {
                        $.ajax({
                            type: "POST",
                            url: baseurl + '/backend/banner/delete',
                            cache: false,
                            dataType: 'json',
                            data: {'ban_id': id},
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
            $('.select_all').on('click', function () {
                if ($(this).prop('checked')) {
                    $('.select_info').prop('checked', true);
                } else {
                    $('.select_info').prop('checked', false);
                }

            });
            $('.select_info').on('click', function () {
                var select_in = $('.select_info');
                var t = 0;
                select_in.each(function () {
                    if (!$(this).prop('checked')) {
                        $('.select_all').prop('checked', false);
                        t = 1;
                        return false;
                    }
                });
                if (t == 0) {
                    $('.select_all').prop('checked', true);
                }
            });
            $('.delete').click(function () {

                var ban_id = $(this).data('id');
                if (!ban_id) {
                    bootbox.alert('<b>Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại</b>', function () {
                        window.location = window.location.href;
                    });
                }

                bootbox.confirm('<b>Bạn có muốn xóa Sản phẩm này không ???</b>', function (result) {
                    if (result) {
                        $.ajax({
                            type: "POST",
                            url: baseurl + '/backend/banner/delete',
                            cache: false,
                            dataType: 'json',
                            data: {'ban_id': ban_id},
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

    }, submitClose: function () {
        $(".bt-save").each(function (i, tag) {
            $(tag).mouseenter(function () {
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
                            url: baseurl + '/backend/content/delete',
                            cache: false,
                            dataType: 'json',
                            data: {'cont_id': id},
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
    add: function () {
        $(document).ready(function () {
            $('.sumo-multiple-select-box').SumoSelect({okCancelInMulti: false});
            if ($('textarea.ban_html').length >= 1) {
                tinymce.init({
                    selector: "textarea.ban_html",
                    // ===========================================
                    // INCLUDE THE PLUGIN
                    // ===========================================

                    plugins: [
                        "customColor advlist autolink lists link image charmap print preview anchor textcolor",
                        "searchreplace visualblocks code fullscreen",
                        "insertdatetime media table contextmenu paste jbimages"
                    ],
                    // ===========================================
                    // PUT PLUGIN'S BUTTON on the toolbar
                    // ===========================================
                    toolbar: "insertfile undo redo | customColor backcolor  | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image jbimages | fullscreen",
                    // ===========================================
                    // SET RELATIVE_URLS to FALSE (This is required for images to display properly)
                    // ===========================================

                    relative_urls: false

                });
            }
//            $("#ban_image").Nileupload({
//                action: baseurl + '/backend/uploader/upload_content?folder=banners&filename=ban_image',
//                size: '3MB',
//                extension: 'jpg,jpeg,gif',
//                progress: $("#progress"),
//                preview: $(".imagesList"),
//                multi: false
//            });
//            $(document).on('click', ".delete", function () {
//                $(this).closest('li').remove();
//            });
        });
    },
};
