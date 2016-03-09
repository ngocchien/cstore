var Content = {
    index: function () {
        $(document).ready(function () {
            Content.del();
            Content.del_all();
            Content.add();
            Content.submitClose();
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
    }, del: function () {
        $(document).ready(function () {
            $('.remove-conten').click(function () {
                var cont_id = $(this).data('id');
                if (!cont_id) {
                    bootbox.alert('<b>Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại</b>', function () {
                        window.location = window.location.href;
                    });
                }

                bootbox.confirm('<b>Bạn có muốn xóa Sản phẩm này không ???</b>', function (result) {
                    if (result) {
                        $.ajax({
                            type: "POST",
                            url: baseurl + '/backend/content/delete',
                            cache: false,
                            dataType: 'json',
                            data: {'cont_id': cont_id},
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
        $('.sumo-multiple-select-box').SumoSelect({okCancelInMulti: false});
        $('.sumo-select-box').SumoSelect({okCancelInMulti: false, triggerChangeCombined: false});
        $(document).ready(function () {
            if ($('textarea.editor').length >= 1) {
                tinymce.init({
                    selector: "textarea.editor",
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
                    toolbar: "insertfile undo redo | customColor backcolor  | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link unlink image jbimages | fullscreen",
                    // ===========================================
                    // SET RELATIVE_URLS to FALSE (This is required for images to display properly)
                    // ===========================================

                    relative_urls: false

                });
            }

            $("#cont_image").Nileupload({
                action: baseurl + '/backend/uploader/upload_content?folder=content&filename=cont_image',
                size: '3MB',
                extension: 'jpg,jpeg,png,gif',
                progress: $("#progress"),
                preview: $(".newsImagesList"),
                multi: false
            });
            $(document).on('click', ".delete", function () {
                $(this).closest('li').remove();
            });
        });
    },
    upload: function () {
        $("#cont_image").Nileupload({
            action: baseurl + '/backend/uploader/upload_content?folder=content&filename=content_image',
            size: '3MB',
            extension: 'jpg,jpeg,png,gif',
            progress: $("#progress"),
            preview: $(".image-list"),
            multi: false
        });
        $(document).on('click', ".delete", function () {
            $(this).closest('li').remove();
        });
    },
    getSelect: function(){
        $('#cate').change(function() {
            var arrCate = $(this).val();
            var cate= '';
            if(jQuery.isEmptyObject(arrCate) == false){
                var i =0;
                $.each(arrCate, function(key , value){
                    cate = cate + value;
                    i += 1;
                    if(arrCate.length != i)
                        cate = cate + ',';
                });
            console.log(cate);
            }
            $('#cate_id').val(cate);
        });
    }
};
