var Product = {
    index: function () {
        $(document).ready(function () {
            $("#frm").validate({
                ignore: '.ignore',
                rules: {
                    prod_name: {
                        required: true,
                        minlength: 3,
                        maxlength: 255
                    },
                    cate_id: {
                        required: true,
                        greaterThanZero: true
                    },
                    bran_id: {
                        required: true,
                        greaterThanZero: true
                    },
                    prod_price: {
                        required: true,
                        greaterThanZero: true
                    },
                    prod_promotion_price: {
                        required: true,
                        greaterThanZero: true
                    },
                    prod_description: {
                        required: true,
                        minlength: 10
                    },
                    prod_detail: {
                        required: true,
                        minlength: 20
                    },
                    tags_name: {
                        required: true
                    },
                    prod_meta_title: {
                        required: true
                    },
                    prod_meta_keyword: {
                        required: true
                    },
                    prod_meta_description: {
                        required: true
                    }
                },
                messages: {
                    prod_name: {
                        required: 'Xin vui lòng nhập tên sản phẩm.',
                        minlength: 'Tên sản phẩm tối thiểu từ 3 kí tự trở lên.',
                        maxlength: 'Tên sản phẩm tối đa 255 kí tự'
                    },
                    cate_id: {
                        required: 'Xin vui lòng chọn Danh mục cho sản phẩm'
                    },
                    bran_id: {
                        required: 'Xin vui lòng chọn Thương hiệu cho sản phẩm'
                    },
                    prod_price: {
                        required: 'Xin vui lòng nhập giá cho sản phẩm'
                    },
                    prod_promotion_price: {
                        required: 'Xin vui lòng nhập giá khuyến mãi cho sản phẩm'
                    },
                    prod_description: {
                        required: 'Xin vui lòng nhập mô tả ngắn cho sản phẩm.',
                        minlength: 'Mô tả ngắn cho sản phẩm ít nhất phải từ 10 ký tự trở lên.'
                    },
                    prod_detail: {
                        required: 'Xin vui lòng nhập nội dung chi tiết cho sản phẩm.',
                        minlength: 'Nội dung chi tiết cho sản phẩm ít nhất phải từ 20 ký tự trở lên.'
                    },
                    tags_name: {
                        required: 'Xin vui lòng chọn tag cho sản phẩm.'
                    },
                    prod_meta_title: 'Meta title không được bỏ trống.',
                    prod_meta_keyword: 'Meta Keywork không được bỏ trống.',
                    prod_meta_description: 'Meta Description không được bỏ trống.'
                }
            });


            /*
             * autocomplete for tags 
             */
            var MIN_LENGTH = 3;
            var listResult = '';

            $('#tags_name').keyup(function () {

                var countries = ["Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua & Deps", "Argentina", "Armenia", "Australia", "Austria",
                    "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia Herzegovina",
                    "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina", "Burma", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Central African Rep",
                    "Chad", "Chile", "People's Republic of China", "Republic of China", "Colombia", "Comoros", "Democratic Republic of the Congo", "Republic of the Congo",
                    "Costa Rica", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Danzig", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor",
                    "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gaza Strip",
                    "The Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hungary",
                    "Iceland", "India", "Indonesia", "Iran", "Iraq", "Republic of Ireland", "Israel", "Italy", "Ivory Coast", "Jamaica", "Japan", "Jonathanland",
                    "Jordan", "Kazakhstan", "Kenya", "Kiribati", "North Korea", "South Korea", "Kosovo", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho",
                    "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta",
                    "Marshall Islands", "Mauritania", "Namibia", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Norway", "Oman", "Pakistan", "Palau",
                    "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russian Federation", "Rwanda", "Samoa",
                    "San Marino", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia",
                    "South Africa", "Spain", "Sri Lanka", "Sudan", "Swaziland", "Sweden", "Switzerland", "Syria,", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga",
                    "Trinidad & Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States of America",
                    "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe"];
                $("#tags_name").shieldAutoComplete({
                    dataSource: {
                        data: countries
                    },
                    minLength: 1
                });
//                var tagName = $.trim($('#tags_name').val());
//                if (tagName.length < MIN_LENGTH) {
//                    return false;
//                }
//                var listTagsId = $('.listTagsId').val();
//
//                $.ajax({
//                    type: 'POST',
//                    cache: false,
//                    dataType: 'json',
//                    url: getTagURL,
//                    data: {
//                        listTagsId: listTagsId,
//                        tagName: tagName
//                    },
//                    beforeSend: function () {
//
//                    },
//                    success: function (rs) {
//                        if (rs.st == 1) {
//                            if (!rs.data) {
//                                return false;
//                            }
//
//                            $.each(rs.data, function (k, v) {
//                                console.log(v.tags_name);
//                            });
//
//                        } else {
//                            bootbox.alert(rs.ms);
//                            return false;
//                        }
//                    }
//                })
            });

            Product.del_all();
            Product.add();
            Product.edit();
            Product.validateNumber();
            Product.submitClose();
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
            tinymce.init({
                selector: 'textarea',
                // ===========================================
                // INCLUDE THE PLUGIN
                // ===========================================

//                plugins: [
//                    "customColor advlist autolink lists link  image charmap print preview anchor textcolor",
//                    "searchreplace visualblocks code fullscreen",
//                    "insertdatetime media table contextmenu paste jbimages"
//                ],
                // ===========================================
                // PUT PLUGIN'S BUTTON on the toolbar
                // ===========================================
                toolbar: "insertfile undo redo | customColor backcolor  | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link unlink image jbimages | fullscreen",
                // ===========================================
                // SET RELATIVE_URLS to FALSE (This is required for images to display properly)
                // ===========================================

                relative_urls: false

            });
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
};
