var TagsCont = {
    index: function () {
        $(document).ready(function () {
            TagsCont.del();
            TagsCont.add();
            TagsCont.submitClose();
            TagsCont.edit_content();
            TagsCont.editContentTags();
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
    add: function () {
        $(document).ready(function () {
            $('.sumo-multiple-select-box').SumoSelect({okCancelInMulti: false});
            $('.sumo-select-box').SumoSelect({okCancelInMulti: false, triggerChangeCombined: false});
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

        $(document).ready(function () {
            $('#cancel').click(function () {
                validator.resetForm();
                clearForm($('#frm'));
            });

        })
    },
    del: function () {
        $(document).ready(function () {
            $('.remove').click(function () {
                TagsID = $(this).attr('rel');
                if (TagsID) {
                    bootbox.confirm('<b>Bạn có muốn xóa danh mục này không ???</b>', function (result) {
                        if (result) {
                            $.ajax({
                                type: "POST",
                                url: baseurl + '/backend/tags/delete',
                                cache: false,
                                dataType: 'json',
                                data: {'TagsID': TagsID},
                                success: function (result) {
                                    if (result.st === 1) {
                                        bootbox.alert('<b>' + result.ms + '</b>', function () {
                                            window.location = window.location.href;
                                        });
                                    } else if (result.st === -1) {
                                        bootbox.alert('<b>' + result.ms + '</b>');
                                    }
                                }
                            });
                        }
                    });
                }
            });
        });
    },
    editContentTags: function () {
        $(document).on('click', '.btn-deleteContnetTags', function () {
            var cont_id = $(this).closest('tr').attr('id');
            var name = $(this).parent().prev().text();
            if (tags_id && cont_id) {

                $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/tags-content/delete-content',
                    cache: false,
                    dataType: 'json',
                    data: {tags_id: tags_id, cont_id: cont_id},
                    success: function (result) {
                        if (result.success === 1) {

                            var html = '';
                            $('tr#' + cont_id).fadeOut('slow', function () {
                                $(this).remove();
                                html += '<tr id="' + cont_id + '">',
                                        html += '<td style="text-align:center"><a class="btn btn-success btn-xs censor btn-addContent"><i class="icon-arrow-left"></i> Thêm</a></td>',
                                        html += '<td>' + name + '</td>',
                                        html += '</tr>',
                                        $('.searchcontent').children('table').children().next().prepend(
                                        $(html).hide().fadeIn('slow'),
                                        $('.none-content').fadeOut('slow')
                                        );
                            });
                        } else if (result.error === 1) {
                            bootbox.alert('<b>' + result.message + '</b>');
                        }
                    }
                });

            } else {
                bootbox.alert('<b>Có lỗi trong quá trình xử lý ...</b>');
            }
        });

        $('.getproducttags').on('click', '.btn-searchProductTags', function () {
            search_producttags = $('.search_producttags').val();
            if (tags_id) {
                $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/tags/searchproducttags',
                    cache: false,
                    data: {tags_id: tags_id, search_name: $.trim(search_producttags)},
                    success: function (data) {
                        if (data) {

                            $('.searchproducttags').html(data);
                        } else {
                            alert('<b>Xảy ra lỗi trong quá trình xử lý ...</b>');
                        }
                    }
                });
            } else {
                bootbox.alert('<b>Có lỗi trong quá trình xử lý ...</b>');
            }
        });

        $('.getproducttags').on('click', '.btn-updateProductTags', function () {
            $(".searchproducttags").find('tbody').animate({//hiệu ứng cập nhật
                opacity: 0.4,
            }, 400, function () {
                $('.getproducttags').load(baseurl + '/backend/tags/getproducttags/', {tags_id: tags_id});
            });
        });
        $('.getproducttags').on('keypress', '.search_producttags', function (event) {
            var keycode = (event.keyCode ? event.keyCode : event.which); //K bít đây là cái quỉ gì
            if (keycode == '13') {  //enter
                $('.btn-searchProductTags').trigger('click');
            }
        });

    },
    edit_content: function () {
        $(document).on('click', '.btn-addContent', function () {
            var cont_id = $(this).closest('tr').attr('id');
            var name = $(this).parent().next().text();
            if (tags_id && cont_id) {
                $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/tags-content/add-content',
                    cache: false,
                    dataType: 'json',
                    data: {'tags_id': tags_id, 'cont_id': cont_id},
                    success: function (result) {
                        if (result.success === 1) {

                            var html = '';
                            $('tr#' + cont_id).fadeOut('slow', function () {
                                $(this).remove();
                                html += '<tr id="' + cont_id + '">',
                                        html += '<td>' + name + '</td>',
                                        html += '<td style="text-align:center"><a class="btn btn-danger btn-xs btn-deleteContnetTags">Loại bỏ <i class="icon-arrow-right"></i></a></td>',
                                        html += '</tr>',
                                        $('.searchcontenttags').children('table').children().next().prepend(
                                        $(html).hide().fadeIn('slow'),
                                        $('.none-contenttags').fadeOut('slow')
                                        );
                            });

                        } else if (result.error === 1) {
                            bootbox.alert('<b>' + result.message + '</b>');
                        }
                    }
                });

            } else {
                bootbox.alert('<b>Có lỗi trong quá trình xử lý ...</b>');
            }
        });
        $('.getproduct').on('click', '.btn-searchProduct', function () {
            search_product = $('.search_product').val();
            if (tags_id) {
                $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/tags/searchproduct',
                    cache: false,
                    data: {tags_id: tags_id, search_name: search_product},
                    success: function (data) {
                        if (data) {
                            $('.searchproduct').html(data);
                        } else {
                            alert('<b>Xảy ra lỗi trong quá trình xử lý ...</b>');
                        }
                    }
                });
            } else {
                bootbox.alert('<b>Có lỗi trong quá trình xử lý ...</b>');
            }
        });

        $('.getproduct').on('click', '.btn-updateProduct', function () {
            $(".searchproduct").find('tbody').animate({//hiệu ứng cập nhật
                opacity: 0.4,
            }, 400, function () {
                $('.getproduct').load(baseurl + '/backend/tags/getproduct/', {tags_id: tags_id});
            });
        });

        $('.getproduct').on('keypress', '.search_product', function (event) {
            var keycode = (event.keyCode ? event.keyCode : event.which); //K bít đây là cái quỉ gì
            if (keycode == '13') {  //enter
                $('.btn-searchProduct').trigger('click');
            }
        });
    },
}
