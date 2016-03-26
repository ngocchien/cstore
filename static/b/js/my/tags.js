var Tags = {
    index: function () {
        $(document).ready(function () {
            tinymce.init({
                selector: "textarea.editor",
                relative_urls: false
            });

            Tags.del();
            Tags.edit_producttags();
            Tags.edit_product();
            Tags.ord();
        });
    },
    del: function () {
        $('.remove').click(function () {
            var tagsId = $(this).attr('rel');
            if (tagsId) {
                bootbox.confirm('<b>Bạn có muốn xóa tags này không ???</b>', function (result) {
                    if (result) {
                        $.ajax({
                            type: "POST",
                            url: baseurl + '/backend/tags/delete',
                            cache: false,
                            dataType: 'json',
                            data: {'TagsID': tagsId},
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
    },
    edit_producttags: function () {
        $('.getproducttags').on('click', '.btn-deleteProductTags', function () {
            var prod_id = $(this).closest('tr').attr('id');
            var name = $(this).parent().prev().prev().text();
            if (tags_id && prod_id) {

                $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/tags/deleteProduct',
                    cache: false,
                    dataType: 'json',
                    data: {tags_id: tags_id, prod_id: prod_id},
                    success: function (result) {
                        if (result.success === 1) {

                            var html = '';
                            $('tr#' + prod_id).fadeOut('slow', function () {
                                $(this).remove();
                                html += '<tr id="' + prod_id + '">',
                                        html += '<td style="text-align:center"><a class="btn btn-success btn-xs censor btn-addProduct"><i class="icon-arrow-left"></i> Thêm</a></td>',
                                        html += '<td>' + name + '</td>',
                                        html += '</tr>',
                                        $('.searchproduct').children('table').children().next().prepend(
                                        $(html).hide().fadeIn('slow'),
                                        $('.none-product').fadeOut('slow')
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
                    url: baseurl + '/backend/tags/getproducttags',
                    cache: false,
                    data: {tags_id: tags_id, search_name: $.trim(search_producttags)},
                    success: function (data) {
                        if (data) {

                            $('#unseenTags').html(data);
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
    edit_product: function () {

        $('.getproduct').on('click', '.btn-addProduct', function () {
            var prod_id = $(this).closest('tr').attr('id');
            var name = $(this).parent().next().text();
            if (tags_id && prod_id) {
                $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/tags/addProduct',
                    cache: false,
                    dataType: 'json',
                    data: {'tags_id': tags_id, 'prod_id': prod_id},
                    success: function (result) {
                        if (result.success === 1) {

                            var html = '';
                            $('tr#' + prod_id).fadeOut('slow', function () {
                                $(this).remove();
                                html += '<tr id="' + prod_id + '">',
                                        html += '<td>' + name + '</td>',
                                        html += '<td class="text-center"> <input style="text-align: center" size="2" class="data-ord" type="text" data-id ="' + prod_id + '" name="ord[' + prod_id + ']" value="0"> </td>',
                                        html += '<td style="text-align:center"><a class="btn btn-danger btn-xs btn-deleteProductTags">Loại bỏ <i class="icon-arrow-right"></i></a></td>',
                                        html += '</tr>',
                                        $('.searchproducttags').children('table').children().next().prepend(
                                        $(html).hide().fadeIn('slow'),
                                        $('.none-producttags').fadeOut('slow')
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
                    url: baseurl + '/backend/tags/getproduct',
                    cache: false,
                    data: {tags_id: tags_id, search_name: search_product},
                    success: function (data) {
                        if (data) {
                            $('#unseen').html(data);
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
    ord: function () {
        $(".bt-update-ord").click(function () {
            var tags_id = $('input[name="tags_id"]').val();
            var listProd = [];
            $('input.data-ord').each(function () {
                listProd.push(($(this).attr("data-id")));
            });
            var ord = $('input.data-ord').serialize() + "&tags_id=" + tags_id + "&listProd=" + listProd;
            $.ajax({
                type: "POST",
                url: baseurl + '/backend/tags/getproducttags',
                cache: false,
                dataType: 'json',
                data: ord,
                success: function (result) {
                    if (result.success === 1) {
                        $("#unseenTags").find('tbody').animate({//hiệu ứng cập nhật
                            opacity: 0.4,
                        }, 400, function () {
                            $('.getproducttags').load(baseurl + '/backend/tags/getproducttags', {tags_id: tags_id});
                        });
                    }
                }
            });
        });
    },
}