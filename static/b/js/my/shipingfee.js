var Shipping = {
    index: function () {
        $(document).ready(function () {
            Shipping.add();
            Shipping.submitClose();
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
    }, add: function () {
        city = $('#city');
        district = $('#district');
        city.change(function () {
            cityID = $(this).val();
            cityName = $(this).find(':selected').text();
            if (cityID) {
                $.ajax({
                    type: "POST",
                    url: baseurl + '/backend/district/get-list',
                    cache: false,
                    dataType: 'json',
                    data: {
                        cityID: cityID
                    },
                    success: function (result) {
                        
                        if (result) {
                            var option = '';
                            $.each(result, function (key, value) {
                                option += '<option value="' + value.dist_id + '">' + value.dist_name + '</option>';
                            });
                            district.html(option);                            
                            district.selectpicker('refresh');
                        } else {
                            bootbox.alert('Không thể lấy dữ liệu Quận / Huyện');
                        }
                    }
                });
            }
        });

    },
    del: function () {
        $(document).ready(function () {
            $('.remove').click(function () {
                ShipFeeID = $(this).attr('rel');
                if (ShipFeeID) {
                    bootbox.confirm('Bạn có muốn xóa không', function (result) {
                        if (result) {
                            $.ajax({
                                type: "POST",
                                url: baseurl + '/backend/ShippingFee/delete',
                                cache: false,
                                dataType: 'json',
                                data: {
                                    'ShipFeeID': ShipFeeID
                                },
                                success: function (result) {
                                    if (result.st == 1) {
                                        window.location = window.location;
                                    } else {
                                        bootbox.alert(result.msg);
                                    }
                                }
                            });
                        }
                    });
                }
            });

        });
    }
}
function clearForm(ele) {
    $(ele).find(':input').each(function () {
        switch (this.type) {
            case 'password':
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
