var LinkCateBrand = {
    index: function () {
        $(document).ready(function () {
            LinkCateBrand.del();
            LinkCateBrand.add();
            LinkCateBrand.submitClose();
            LinkCateBrand.getSelect();
            LinkCateBrand.loadData();
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
                LinkCateBrandID = $(this).attr('rel');
                if (LinkCateBrandID) {
                    bootbox.confirm('<b>Bạn có muốn xóa meta này không ???</b>', function (result) {
                        if (result) {
                            $.ajax({
                                type: "POST",
                                url: baseurl + '/backend/LinkCategoryBrand/delete',
                                cache: false,
                                dataType: 'json',
                                data: {'LinkCateBrandID': LinkCateBrandID},
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
        $('.sumo-multiple-select-box').SumoSelect({okCancelInMulti: false});
        $('.sumo-select-box').SumoSelect({okCancelInMulti: false, triggerChangeCombined: false});
        $(document).ready(function () {
            if ($('textarea').length >= 1) {
                tinymce.init({
                    selector: "textarea",
                    // ===========================================
                    // INCLUDE THE PLUGIN
                    // ===========================================

                    plugins: [
                        "advlist autolink lists link image charmap print preview anchor textcolor",
                        "searchreplace visualblocks code fullscreen",
                        "insertdatetime media table contextmenu paste jbimages"
                    ],
                    // ===========================================
                    // PUT PLUGIN'S BUTTON on the toolbar
                    // ===========================================
                    toolbar: "insertfile undo redo | forecolor backcolor | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image jbimages | fullscreen",
                    // ===========================================
                    // SET RELATIVE_URLS to FALSE (This is required for images to display properly)
                    // ===========================================
                    relative_urls: false
                });
            }
            LinkCateBrand.loadData();
        })
    },
    loadData: function () {  
        $ ('select[name="cateId"]').change( function () {
            var main_cate = $(this).val();
            console.log(main_cate);
            $.ajax({
                type: "POST",
                url: baseurl + '/backend/link-category-brand/load-brand',
                cache: false,
                dataType: 'json',
                data: {main_cate: main_cate},
                beforeSend: function () {
                    $('img.loading').css('display', 'block');
                },
                success: function (result) {                      
                    setTimeout(function(){$('.brand_id').selectpicker();},500);
                    if (!result.error) {
                        $('.brand_id').html(result.success);
                    } else {
                        bootbox.alert(result.message);
                    }
                    $('img.loading').css('display', 'none');
                }
               // comlete: function ()
            });
        });
    },
    getSelect: function(){
        $('#cate').change(function() {
            var arrCate = $(this).val();
            var cate= '';
            if(jQuery.isEmptyObject(arrCate) == false){
                var i =0;
                $.each(arrCate, function(key , value){
                    cate = cate + value;
                    i += 1;
                    if(arrCate.length != i)
                        cate = cate + ',';
                });
            //console.log($('#cate_id').val());
            }
            $('#cate_id').val(cate);
        });
        $('#brand').change(function() {
            var arrbrand = $(this).val();
            var cate= '';
            if(jQuery.isEmptyObject(arrbrand) == false){
                var i =0;
                $.each(arrbrand, function(key , value){
                    cate = cate + value;
                    i += 1;
                    if(arrbrand.length != i)
                        cate = cate + ',';
                });
                console.log(cate);
            }
            $('#brand_id').val(cate);
            //console.log($('#cate_id').val());
        });
    }
}


