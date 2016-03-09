var Provider = {
    index: function () {
        $(document).ready(function () {
            Provider.add();
            Provider.submitClose();
            $('.remove').click(function () {
                var intProviderID = $(this).attr('rel');
                bootbox.dialog({
                    message: 'Xóa nhà cung cấp này?',
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
                                    url: baseurl + '/backend/provider/delete',
                                    dataType: 'json',
                                    cache: false,
                                    data: {
                                        'providerID': intProviderID
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
    add: function () {
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
};
