var Product = {
    index: function () {
        $(document).ready(function () {
//            Product.del();
            Product.del_all();
            Product.add();
            Product.edit();
            Product.validateNumber();

            Product.submitClose();
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
//    del: function () {
//        $(document).ready(function () {
//            $('.remove').click(function () {
//
//                var productID = $(this).data('id');
//                if (!productID) {
//                    bootbox.alert('<b>Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại</b>', function () {
//                        window.location = window.location.href;
//                    });
//                }
//
//                bootbox.confirm('<b>Bạn có muốn xóa Sản phẩm này không ???</b>', function (result) {
//                    if (result) {
//                        $.ajax({
//                            type: "POST",
//                            url: baseurl + '/backend/product/delete',
//                            cache: false,
//                            dataType: 'json',
//                            data: {'productID': productID},
//                            success: function (result) {
//                                if (result.success === 1) {
//                                    bootbox.alert('<b>' + result.message + '</b>', function () {
//                                        window.location = window.location.href;
//                                    });
//                                } else if (result.error === 1) {
//                                    bootbox.alert('<b>' + result.message + '</b>');
//                                }
//                            }
//                        });
//                    }
//                });
//
//            });
//        });
//    },
    del_all: function () {
        $(document).ready(function () {
            $('.remove-all').click(function () {
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
                            url: baseurl + '/backend/product/delete',
                            cache: false,
                            dataType: 'json',
                            data: {'productID': id},
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
                        "customColor advlist autolink lists link  image charmap print preview anchor textcolor",
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
        });
    },
    edit: function () {
        $(document).ready(function () {
            $('.btn-edit').click(function () {
                productID = $(this).data('id');
                currentPrice = $(this).parents('tr').children('.productPrice').text();
                $(this).parents("tr").children('.productPrice').html('<span class="help-inline" style="font-size: 10px;color: #D70000;font-weight: bold">Giá: ' + $.trim(currentPrice) + '</span><input type="text" class="text-right form-control input-sm m-bot15" value="0" />');
                $(this).next().show();
                $(this).next().next().show();
                $(this).hide();
            });
            $(".denied").click(function () {
                $(this).parents("tr").children('.productPrice').html($.trim(currentPrice));
                $(this).prev().hide();
                $(this).prev().prev().show();
                $(this).hide();
            });
            $(".check").click(function () {
                newPrice = $(this).parents("tr").find('.productPrice input').val();
                console.log(newPrice);
                if (!newPrice) {
                    bootbox.alert('<b> Chưa Nhập giá cho sản phẩm !!! </b>');
                    return;
                }
                if (!Product.validateNumber(newPrice)) {
                    bootbox.alert('<b> Giá sản phẩm phải là số !!! </b>');
                    return;
                }
                thisObj = $(this);
                bootbox.confirm('<b> Bạn có chắc chắn muốn sửa giá sản phẩm này không ???</b>', function (result) {
                    $.ajax({
                        type: "POST",
                        url: baseurl + '/backend/product/edit-ajax',
                        cache: false,
                        dataType: 'json',
                        data: {
                            'productID': productID,
                            'newPrice': newPrice,
                        },
                        success: function (result) {
                            if (result.st == 1) {
                                bootbox.alert('<b>' + result.ms + '</b>', function () {
                                    thisObj.parents("tr").children('.productPrice').html(result.data);
                                    $(thisObj).prev().show();
                                    $(thisObj).next().hide();
                                    $(thisObj).hide();
                                });
                            }
                            if (result.st == -1) {
                                bootbox.alert('<b>' + result.ms + '</b>');
                            }
                        }
                    });
                });

            })
        });
    },
    upload: function () {
        $("#prod_image").Nileupload({
            action: baseurl + '/backend/uploader/upload_content?folder=product&filename=prod_image',
            size: '3MB',
            extension: 'jpg,jpeg,png,gif',
            progress: $("#progress"),
            preview: $(".image-list"),
            multi: false
        });
        $("#prod_image_sub").Nileupload({
            action: baseurl + '/backend/uploader/upload_content?folder=product&filename=prod_image_sub',
            size: '3MB',
            extension: 'jpg,jpeg,png,gif',
            progress: $("#sub-progress"),
            preview: $(".sub-image-list"),
            multi: true
        });

        $(document).on('click', ".delete", function () {
            $(this).closest('li').remove();
        });

        $(".main_cate_id").change(function () {
            Product.getProperties($(this).val());
        });
    },
    getProperties: function (id) {
        $.ajax({
            type: "POST",
            url: baseurl + '/backend/product/get_property',
            cache: false,
            dataType: 'json',
            data: {'cate_id': id},
            success: function (result) {
                if (result.st == 1) {
                    $(".prop-data-load").html(result.data);
                    $('.sumo-select-box').SumoSelect({okCancelInMulti: false, triggerChangeCombined: false});
                } else if (result.st == 0) {

                } else {
                    bootbox.alert('<b>' + result.msg + '</b>');
                }
            }
        });
    },
    validateNumber: function (txtNumber) {
        var filter = /^[0-9-+]+$/;
        if (filter.test(txtNumber)) {
            return true;
        } else {
            return false
        }
    },
    getSelect: function(){
        $('#cate').change(function() {
//            alert('aaaaaa');
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
            //console.log($('#cate_id').val());
            }
            $('#cate_id').val(cate);
        });
        $('#brand').change(function() {
            var arrbrand = $(this).val();
            var cate= '';
            if(jQuery.isEmptyObject(arrbrand) == false){
                var i =0;
                $.each(arrbrand, function(key , value){
                    cate = cate + value;
                    i += 1;
                    if(arrbrand.length != i)
                        cate = cate + ',';
                });
                console.log(cate);
            }
            $('#brand_id').val(cate);
            //console.log($('#cate_id').val());
        });
    }
};
