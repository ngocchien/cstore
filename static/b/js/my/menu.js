var Menu = {
    index: function () {
        $(document).ready(function () {
            Menu.del();
            Menu.add();
            Menu.submitClose();
        });
    }, 
    
    submitClose: function () {
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
                menuID = $(this).attr('rel');
                if (menuID) {
                    bootbox.confirm('<b>Bạn có muốn xóa danh mục này không ???</b>', function (result) {
                        if (result) {
                            $.ajax({
                                type: "POST",
                                url: baseurl + '/backend/Menu/delete',
                                cache: false,
                                dataType: 'json',
                                data: {'menuID': menuID},
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
    
    add : function () {
        $(document).on('change','.menutype',function(){
            value = $.trim($('.menutype').val());
            tmp='';
            if(value==0){
                tmp =  '<option value="1">Trên cùng</option>\n\
                        <option value="2">footer cột 1</option>\n\
                        <option value="3">footer cột 2</option>\n\
                        <option value="4">footer cột 3</option>\n\
                        <option value="5">footer cột 4</option>\n\
                        <option value="6">footer cột 5</option>';
            }
            if(value==1){
                tmp =  '<option value="1">footer trên </option>\n\
                        <option value="2">footer dưới 1</option>\n\
                        <option value="3">footer dưới 2</option>\n\
                        <option value="4">footer dưới 3</option>\n\
                        <option value="5">footer dưới 4</option>'
            }
            $('.localtion').html(tmp);
        })
    }
};
