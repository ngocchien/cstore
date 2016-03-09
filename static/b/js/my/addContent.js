function deleteContent(id_news_cate, cont_id, page) {
    if (cont_id == '') {  // xóa những bài viết đã check
        var value = '';
        $.each($("input[name='cont_id[]']:checked"), function () {
            value += $(this).val() + ',';
        });
        cont_id = value.substring(0, value.length - 1);
        if (cont_id == '') {
            return;
        }
    }
    if (cont_id) {
        bootbox.confirm('<b>Bạn có muốn xóa bài viết này không ???</b>', function (result) {
            if (result) {
                $.ajax({
                    type: 'POST',
                    url: baseurl + '/backend/news-category/delete-content',
                    data: {cont_id: cont_id, id_news_cate: id_news_cate},
                    dataType: 'json',
                    success: function (result) {
                        if (result.success === 1) {
//                        $.each(cont_id.split(','), function (key, value) {
//                            $('#' + value).fadeOut({duration: 'slow'});
//                        });
                            $('.listContent').load(baseurl + '/backend/news-category/content', {id_news_cate: id_news_cate, page: page, s: s});
                        } else {
                            bootbox.confirm(result.message);
                        }
                    }
                })
            }
        })
    }
}

function addContent() {
    var cont_id = $('#contentId').val();
    if (cont_id == '') {
        var error = 'Lỗi ... không được để trống';
        $('.get-error').html(error);
        return;
    }
    $.each(cont_id.split(','), function (key, value) {
        if (isNaN(value)) {
            var error = 'Lỗi ... chỉ được phép nhập ID';
            $('.get-error').html(error);
            return;
        }
    });
    $.ajax({
        type: 'POST',
        url: baseurl + '/backend/news-category/add-content',
        data: {cont_id: cont_id, id_news_cate: id_news_cate},
        dataType: 'json',
        success: function (result) {
            if (result.success === 1) {
                $("#contentId").val("");
                $(".tag").remove();
                $('.get-error').html("");
                $('.listContent').load(baseurl + '/backend/news-category/content', {id_news_cate: id_news_cate, s: s});
            } else {
                alert('Lỗi');
            }
        }
    })
}

function refresh() {
    $("#unseen").find('tbody').animate({//hiệu ứng cập nhật
        opacity: 0.4,
    }, 400, function () {
        $('.listContent').load(baseurl + '/backend/news-category/content', {id_news_cate: id_news_cate, s: s});
    });
}

function search() {
    s = $('.s').val();
    $('.listContent').load(baseurl + '/backend/news-category/content', {id_news_cate: id_news_cate, s: s});
}

function loadPage(page, action) {
    $('.listContent').load(baseurl + '/backend/news-category/content', {id_news_cate: id_news_cate, page: page, s: s});
}
;