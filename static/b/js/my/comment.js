var Comment = {
    index: function () {
        $(document).ready(function () {
            Comment.censor();
            Comment.del();
            Comment.view();
            Comment.edit();
            Comment.add();
            Comment.approve();
        });
    },
    censor: function () {
        $(document).ready(function () {
            $('.censor').click(function () {
                var _parent = $(this).parent().parent();
                var commID = $(this).data('id');
                if (!commID) {
                    bootbox.alert('<b>Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại</b>', function () {
                        window.location = window.location.href;
                    });
                }
                thisObj = $(this);
                $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/comment/censor',
                    cache: false,
                    data: {
                        'comm_id': commID
                    },
                    success: function (result) {
                        result = $.parseJSON(result);
                        if (result.error == 0) {
                            thisObj.parents("tr").children('.commStatus').html('Đã duyệt');
                            thisObj.hide();
                        }
                        else {
                            bootbox.alert('<b>Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại</b>', function () {
                                window.location = window.location.href;
                            });
                        }
                    }
                });
            });
        });
    },
    view: function () {
        $(document).ready(function () {
            $('.view').click(function () {
                var _parent = $(this).parent().parent();
                var commID = $(this).data('id');
                if (!commID) {
                    bootbox.alert('<b>Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại</b>', function () {
                        window.location = window.location.href;
                    });
                }
                thisObj = $(this);
                $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/comment/view',
                    cache: false,
                    data: {
                        'comm_id': commID
                    },
                    success: function (result) {
                        result = $.parseJSON(result);
                        if (result.error == 0) {
                            thisObj.parents("tr").children('.commStatus').html('Đã duyệt');
                            thisObj.hide();
                        }
                        else {
                            bootbox.alert('<b>Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại</b>', function () {
                                window.location = window.location.href;
                            });
                        }
                    }
                });
            });
        });
    },
    edit: function () {
        $(document).ready(function () {
            $('.comm-edit').click(function () {
                
                var commID = $(this).data('id');
                if (!commID) {
                    bootbox.alert('<b>Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại</b>', function () {
                        window.location = window.location.href;
                    });
                }
//                thisObj = $(this);
                $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/comment/editComment',
                    cache: false,
                    data: {
                        'comm_id': commID
                    },
                    success: function (result) {
                        result = $.parseJSON(result);
                        if (result.error == 0) {
                            var selected1 = '';
                            var selected2 = '';
                            if(result.data.comm_status == 1)
                                selected1 = 'selected';
                            else selected2 = 'selected';
                            bootbox.dialog({
                                //message: '<textarea name="commContent" id="commContent" class="form-control editor" value="" placeholder="Bình luận">' + result.data.comm_content + '</textarea>',
                                message: 
                                        '<div class="row" style="margin-bottom: 5px;">\n\
                                            <label for="cont_slug" class="col-lg-2 col-md-2 col-sm-2 control-label" value="">Fullname</label>\n\
                                            <div class="col-lg-6">\n\
                                                <input type="text" value="'+ result.data.user_fullname +'"class="form-control" id="user_fullname">\n\
                                            </div>\n\
                                        </div>\n\
                                        <div class="row" style="margin-bottom: 5px;">\n\
                                            <label for="cont_slug" class="col-lg-2 col-md-2 col-sm-2 control-label" value="">Email</label>\n\
                                            <div class="col-lg-6">\n\
                                                <input type="text" value="'+ result.data.user_email +'" class="form-control" id="user_email">\n\
                                            </div>\n\
                                        </div>\n\
                                        <div class="row" style="margin-bottom: 5px;">\n\
                                            <label for="user_type" class="col-sm-2 control-label" value="">Tình trạng</label>\n\
                                            <div class="col-lg-6">\n\
                                                <select id="comm_status" class="form-control">\n\
                                                    <option value="1"' + selected1 +'>Đã duyệt</option>\n\
                                                    <option value="0"' + selected2 +'>Chưa duyệt</option>\n\
                                                </select>\n\
                                            </div>\n\
                                        </div>\n\
                                        <div class="row" style="margin-bottom: 5px;">\n\
                                            <label for="comm_content" class="col-sm-4 control-label">Nội dung comment:</label>\n\
                                            <div class="col-sm-12">\n\
                                                <textarea placeholder="Nhập bình luận" rows="6" value="" id="edit_content" class="form-control">'+ result.data.comm_content +'</textarea>\n\
                                            </div>\n\
                                        </div>\n\
                                        <script>\n\
                                            Comment.loadEditor();\n\
                                        </script>',
                                title: "Chỉnh sửa bình luận",
                                buttons: {
                                    success: {
                                        label: "Cập nhật",
                                        className: "btn-success",
                                        callback: function () {
                                            cont = $("#edit_content").val();
                                            console.log(cont);
                                            $.ajax({
                                                type: "POST",
                                                url: baseurl + '/backend/comment/editComment',
                                                cache: false,
                                                data: {
                                                    'comm_id': commID,
                                                    'comm_content': $("#edit_content").val(),
                                                    'comm_status': $("#comm_status").val(),
                                                    'user_fullname': $("#user_fullname").val(),
                                                    'user_email': $("#user_email").val(),
                                                },
                                                success: function (result) {
                                                    result = $.parseJSON(result);
                                                    if (result.error == 0) {
                                                        bootbox.alert('<b>' + result.message + '</b>', function () {
                                                            window.location = window.parent.location.pathname;
                                                        });
                                                    } else {
                                                        bootbox.alert('<b>Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại</b>', function () {
                                                            window.location = window.location.href;
                                                        });
                                                    }

                                                }
                                            });
                                        }
                                    },
                                    danger: {
                                        label: "Thoát",
                                        className: "btn-danger",
                                        callback: function () {

                                        }
                                    }
                                }
                            });
                        }
                        else {
                            bootbox.alert('<b>Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại</b>', function () {
                                window.location = window.location.href;
                            });
                        }
                        
                        
                    }
                });
            });
        });
    },
    del: function () {
        $(document).ready(function () {
            $('.delete').click(function () {
                var commID = $(this).data('id');
                if (commID) {
                    bootbox.confirm('<b>Bạn có muốn xóa Bình luận này không ???</b>', function (result) {
                        if (result) {
                            $.ajax({
                                type: "POST",
                                url: baseurl + '/backend/comment/delete',
                                cache: false,
                                dataType: 'json',
                                data: {'comm_id': commID},
                                success: function (result) {
                                    console.log(result);
                                    if (result.success === 1) {
                                        bootbox.alert('<b>' + result.message + '</b>', function () {
                                            window.location = window.parent.location.pathname;
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
//            $('.sumo-multiple-select-box').SumoSelect({okCancelInMulti: false});
//            $('.sumo-select-box').SumoSelect({okCancelInMulti: false, triggerChangeCombined: false});
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
    loadEditor: function () {
        $(document).ready(function () {
//            $('.sumo-multiple-select-box').SumoSelect({okCancelInMulti: false});
//            $('.sumo-select-box').SumoSelect({okCancelInMulti: false, triggerChangeCombined: false});
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
    approve: function(){
        $('.comm-approve').click(function(){
            var id = $(this).data('id');
            console.log(id);
        });
    }
};
