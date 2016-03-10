
var Brand = {
    index: function () {
        $(document).ready(function () {
            Brand.del();
            Brand.add();
            Brand.submitClose();
            Brand.nextinput();
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
            $('.remove').click(function () {
                var brandID = $(this).attr('rel');
                if (brandID) {
                    bootbox.confirm('<b>Bạn có muốn xóa thương hiệu này không ???</b>', function (result) {
                        if (result) {
                            $.ajax({
                                type: "POST",
                                url: baseurl + '/backend/brand/delete',
                                cache: false,
                                dataType: 'json',
                                data: {
                                    brandID: brandID
                                },
                                success: function (rs) {
                                    if (rs.st === 1) {
                                        bootbox.alert('<b>' + rs.ms + '</b>', function () {
                                            window.location = window.location.href;
                                        });
                                    } else {
                                        bootbox.alert('<b>' + rs.ms + '</b>');
                                    }
                                }
                            });
                        }
                    });
                }
            });
        });
    },
    add: function () {
        $('.sumo-select-box').SumoSelect({okCancelInMulti: false, triggerChangeCombined: false});
        $(document).ready(function () {
            if ($('textarea').length >= 1) {
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
                    toolbar: "insertfile undo redo | customColor backcolor  | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image jbimages | fullscreen",
                    // ===========================================
                    // SET RELATIVE_URLS to FALSE (This is required for images to display properly)
                    // ===========================================

                    relative_urls: false

                });
            }
        })
    },
    upload: function () {
        $("#cate_image").Nileupload({
            action: baseurl + '/backend/uploader/upload_content?folder=cate&filename=cate_image',
            size: '3MB',
            extension: 'jpg,jpeg,png,gif',
            progress: $("#progress"),
            preview: $(".image-list"),
            multi: false
        });
        $(document).on('click', ".delete", function () {
            $(this).closest('li').remove();
        });

        $(".main_cate_id").change(function () {
            // alert("thay doi");
        });
    },
    nextinput: function () {
        var inputArr = [];
        $("#frm input,.SumoSelect").each(function (i, tag) {
            inputArr[i] = tag;
            console.log(tag);
            $(tag).keypress(function (event) {
                if (event.which == '13') {
                    $(inputArr[i + 1]).focus();
                }
            });
        });
    }
};
