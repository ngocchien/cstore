var Category = {
    index: function () {
        $(document).ready(function () {
            Category.del();
            Category.add();
            Category.ord();
            Category.searchProduct();
            Category.submitClose();
            Category.nextinput();
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
                                url: baseurl + '/backend/category/delete',
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
    },
    ord: function () {
        $(".bt-update-ord").click(function () {
            var listProd = [];
            $('input.data-ord').each(function() { 
                //listProd += ($(this).attr("data-id")) + ',';
                listProd.push(($(this).attr("data-id")));
            });
            var id_cate = $('input[name="id_cate"]').val();
            var ord = $('input.data-ord').serialize() + "&id_cate=" + id_cate + "&listProd=" + listProd;
            console.log(ord);
            $.ajax({
                type: "POST",
                url: baseurl + '/backend/category/product',
                cache: false,
                dataType: 'json',
                data: ord,
                success: function (result) {
                    if (result.success === 1) {
                        $("#unseen").find('tbody').animate({//hiệu ứng cập nhật
                            opacity: 0.4,
                        }, 400, function () {
                            $('.listProducts').load(baseurl + '/backend/category/product', {id_cate: id_cate});
                        });
                    }
                }
            });
        });
    },
    searchProduct: function () {
        $(".btn-search").click(function () {
            var sort = $('select[name="sort"]').val();
            var s = $('input[name="s"]').val();
            console.log(sort);
            $('.listProducts').load(baseurl + '/backend/category/product', {id_cate: id_cate, s: s, sort: sort});
        });
    },
    nextinput: function () {
        var inputArr = [];
        $("#frm input,.SumoSelect,.textedit,.tabselect").each(function (i, tag) {
            inputArr[i] = tag;
            $(tag).keypress(function (event) {
                if (event.which == '13') {
                    //console.log($(inputArr[i + 1]).find('.bootstrap-select').html());
                    //if($(inputArr[i + 1]).attr('da-select')==true){
                   //     $(inputArr[i + 1]).find('.bootstrap-select').addClass('open');
                   // }
                    $(inputArr[i + 1]).focus();
                }
            });
        });
        
    }
};
