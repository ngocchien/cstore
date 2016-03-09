var Subscribe = {
    index: function () {
        $(document).ready(function () {
            Subscribe.del();
            Subscribe.excel();
        });
    },
    del: function () {
        $(document).ready(function () {
            $('.remove').click(function () {
                subscribeID = $(this).attr('rel');
                if (subscribeID) {
                    bootbox.confirm('<b>Bạn có muốn xóa email đăng ký này không ???</b>', function (result) {
                        if (result) {
                            $.ajax({
                                type: "POST",
                                url: baseurl + '/backend/subscribe/delete',
                                cache: false,
                                dataType: 'json',
                                data: {'subscribeID': subscribeID},
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
};
