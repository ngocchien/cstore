var Categorynews = {
    index: function () {
        $(document).ready(function () {
            Categorynews.del();
            Categorynews.add();
            Categorynews.submitClose();
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
                categoryID = $(this).attr('rel');
                if (categoryID) {
                    bootbox.confirm('<b>Bạn có muốn xóa danh mục này không ???</b>', function (result) {
                        if (result) {
                            $.ajax({
                                type: "POST",
                                url: baseurl + '/backend/NewsCategory/delete',
                                cache: false,
                                dataType: 'json',
                                data: {'categoryID': categoryID},
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
                }
            });
        });
    },
    add: function () {
        $(document).ready(function () {
            //$('.sumo-select-box').SumoSelect({okCancelInMulti: false, triggerChangeCombined: false});
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
    }
};
