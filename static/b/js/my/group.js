var Group = {
    index: function () {
        $(document).ready(function () {
            $('body').on('click', '.delete', function () {
                $(this).parents('li').remove();
            });
            Group.del();
            Group.submitClose();
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
        $('.remove').click(function () {
            groupID = $(this).attr('rel');
            if (groupID) {
                bootbox.confirm('Bạn có muốn xóa nhóm này không', function (result) {
                    if (result) {
                        $.ajax({
                            type: "POST",
                            url: baseurl + '/backend/group/delete',
                            cache: false,
                            dataType: 'json',
                            data: {
                                'grou_id': groupID
                            },
                            success: function (result) {
                                if (result) {
                                    window.location = window.location;
                                } else {
                                    bootbox.alert('Không thể xóa nhóm này');
                                }
                            }
                        });
                    }
                });
            }
        });

    }
}
function clearForm(ele) {
    $(ele).find(':input').each(function () {
        switch (this.type) {
            case 'select-multiple':
            case 'select-one':
            case 'text':
            case 'textarea':
                $(this).val('');
                break;
            case 'checkbox':
            case 'radio':
                this.checked = false;
        }
    });
}
