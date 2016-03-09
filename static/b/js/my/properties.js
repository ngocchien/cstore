var Properties = {
    index: function () {
        $(document).ready(function () {
            Properties.del();
            Properties.submitClose();
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
                propertiesID = $(this).attr('rel');
                if (propertiesID) {
                    bootbox.confirm('<b>Bạn có muốn xóa thuộc tính này không ???</b>', function (result) {
                        if (result) {
                            $.ajax({
                                type: "POST",
                                url: baseurl + '/backend/properties/delete',
                                cache: false,
                                dataType: 'json',
                                data: {'propertiesID': propertiesID},
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

//                            $.ajax({
//                                type: "POST",
//                                url: baseurl + '/backend/properties/delete',
//                                cache: false,
//                                dataType: 'json',
//                                data: {'propertiesID': propertiesID},
//                                success: function (result) {
//                                    if (result.success === 1) {
//                                        bootbox.alert('<b>' + result.message + '</b>', function () {
//                                            window.location = window.location.href;
//                                        });
//                                    } else if (result.error === 1) {
//                                        bootbox.alert('<b>' + result.message + '</b>');
//                                    }
//                                }
//                            });
                        }
                    });
                }
            });
        });
    }
};
