function deleteProduct(id_brand, id_prod, page) {
    if (id_prod == '') {  // xóa những sp đã check
        var value = '';
        $.each($("input[name='prod_id[]']:checked"), function () {
            value += $(this).val() + ',';
        });
        id_prod = value.substring(0, value.length - 1);
        if (id_prod == '') {
            return;
        }
    }
    if (id_prod) {
        bootbox.confirm('<b>Bạn có muốn xóa sản phẩm này không ???</b>', function (result) {
            if (result) {
                $.ajax({
                    type: 'POST',
                    url: baseurl + '/backend/brand/delete-product',
                    data: {id_prod: id_prod, id_brand: id_brand},
                    dataType: 'json',
                    success: function (result) {
                        if (result.success === 1) {
//                        $.each(id_prod.split(','), function (key, value) {
//                            $('#' + value).fadeOut({duration: 'slow'});
//                        });
                            $('.listProducts').load(baseurl + '/backend/brand/product', {id_brand: id_brand, page: page, s: s, sort: sort});
                        } else {
                            bootbox.confirm(result.message);
                        }
                    }
                })
            }
        })
    }
}

function addProduct() {
    var id_prod = $('#contentId').val();
    if (id_prod == '') {
        var error = 'Lỗi ... không được để trống';
        $('.get-error').html(error);
        return;
    }
    $.each(id_prod.split(','), function (key, value) {
        if (isNaN(value)) {
            var error = 'Lỗi ... chỉ được phép nhập ID';
            $('.get-error').html(error);
            return;
        }
    });
    $.ajax({
        type: 'POST',
        url: baseurl + '/backend/brand/add-product',
        data: {id_prod: id_prod, id_brand: id_brand},
        dataType: 'json',
        success: function (result) {
            if (result.success === 1) {
                $("#contentId").val("");
                $(".tag").remove();
                $('.get-error').html("");
                $('.listProducts').load(baseurl + '/backend/brand/product', {id_brand: id_brand, s: s, sort: sort});
            } else {
                alert('Lỗi');
            }
        }
    })
}

function sorting() {
    var sort = $('input.sort').serialize() + "&id_brand=" + id_brand;
    $.ajax({
        type: "POST",
        url: baseurl + '/backend/brand/sort',
        dataType: 'json',
        data: sort,
        success: function (result) {
            if (result.success === 1) {
                $("#unseen").find('tbody').animate({//hiệu ứng cập nhật
                    opacity: 0.4,
                }, 400, function () {
                    $('.listProducts').load(baseurl + '/backend/brand/product', {id_brand: id_brand});
                });
            }
        }
    });
}


function refresh() {
    $("#unseen").find('tbody').animate({//hiệu ứng cập nhật
        opacity: 0.4,
    }, 400, function () {
        $('.listProducts').load(baseurl + '/backend/brand/product', {id_brand: id_brand, s: s, sort: sort});
    });
}

function search() {
    s = $('.s').val();
    sort = $('select.select-sort').find(':selected').val();

    $('.listProducts').load(baseurl + '/backend/brand/product', {id_brand: id_brand, s: s, sort: sort});
}

function loadPage(page, action) {
    $('.listProducts').load(baseurl + '/backend/brand/product', {id_brand: id_brand, page: page, s: s, sort: sort});
}
;