var Template = {
    index: function () {
        $(document).ready(function () {
            $('.remove-template').on('click', function () {
                var tem_id = $(this).data('id');
                if (!tem_id) {
                    bootbox.alert('<b>Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại</b>', function () {
                        window.location = window.location.href;
                    });
                }
                bootbox.confirm('<b>Bạn có muốn xóa template này không ???</b>', function (result) {
                    if (result) {
                        $.ajax({
                            type: "POST",
                            url: baseurl + '/backend/template/delete',
                            cache: false,
                            dataType: 'json',
                            data: {'tem_id': tem_id},
                            success: function (result) {
                                if (result.success === 1) {
                                    window.location = window.location.href;
                                } else if (result.error === 1) {
//                                    bootbox.alert('<b>' + result.message + '</b>');
                                }
                            }
                        });
                    }
                });
            });
            $('.delete-all-template').on('click', function () {
                var id = [];
                $(".data-id").find("input:checked").each(function (i, tag) {
                    id[i] = $(tag).val();
                });
                if (id.length < 1) {
                    bootbox.alert('<b>Vui lòng chọn ít nhất một template để xóa</b>', function () {
                    });
                    return;
                }
                bootbox.confirm('<b>Bạn có muốn xóa những template này không ???</b>', function (result) {
                    if (result) {
                        $.ajax({
                            type: "POST",
                            url: baseurl + '/backend/template/delete',
                            cache: false,
                            dataType: 'json',
                            data: {'tem_id': id},
                            success: function (result) {
                                if (result.success === 1) {
                                    window.location = window.location.href;
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
    menu: function () {
        $(document).ready(function () {
            /*
             * Insert a 'details' column to the table
             */
            var nCloneTh = document.createElement('th');
            var nCloneTd = document.createElement('td');
            nCloneTd.innerHTML = '<img src="' + STATIS_URL + '/b/images/details_open.png">';
            nCloneTd.className = "center";

            $('#table-menu-info thead tr').each(function () {
                this.insertBefore(nCloneTh, this.childNodes[0]);
            });

            $('#table-menu-info tbody tr').each(function () {
                this.insertBefore(nCloneTd.cloneNode(true), this.childNodes[0]);
            });

            /*
             * Initialse DataTables, with no sorting on the 'details' column
             */
            var oTable = $('#table-menu-info').dataTable({
                "aoColumnDefs": [
                    {"bSortable": false, "aTargets": [0]}
                ],
//                "bSort": false
                "aaSorting": [[1, 'asc']]
            });

            /* Add event listener for opening and closing details
             * Note that the indicator for showing which row is open is not controlled by DataTables,
             * rather it is done here
             */
            $(document).on('click', '.save_menu', function () {
                var _that = $(this).closest('form');
                var _key = $(this).data('key');
                var _loca = $(this).data('location');
                var row = oTable.fnGetPosition($(this).closest('tr').prev().get(0));
                var name = _that.find('.name').val();
                var url = _that.find('.url').val();
                var sort = _that.find('.sort').val();
                $.ajax({
                    type: "POST",
                    url: baseURL + '/backend/template/edit',
                    cache: false,
                    dataType: 'json',
                    data: _that.serialize() + '&tem_id=' + tem_id + '&key=' + _key + '&loca=' + _loca + '&action=edit',
                    beforeSend: function () {

                    },
                    success: function (result) {
                        oTable.fnUpdate(name, row, 3);
                        oTable.fnUpdate(url, row, 4);
                        oTable.fnUpdate(sort, row, 5);
//                                   console.log;
                    }
                });
                return false;
            });
            $(document).on('click', '.remove_menu', function () {
                var key = $(this).data('key');
                var loca = $(this).data('loca');
                var _that = $(this).closest('tr').get(0);
                bootbox.confirm("Bạn có muốn xóa menu này?", function (result1) {
                    if (result1) {
                        $.ajax({
                            type: "POST",
                            url: baseURL + '/backend/template/edit',
                            cache: false,
                            dataType: 'json',
                            data: {
                                key: key,
                                loca: loca,
                                action: 'delete',
                                tem_id: tem_id
                            },
                            beforeSend: function () {
                            },
                            success: function (result) {
                                oTable.fnDeleteRow(oTable.fnGetPosition(_that));
                            }
                        });
                    }
                });
            });
            $(document).on('click', '.add_menu', function () {
                var html = $("#html_add_menu_hidden").clone(true).removeAttr('id').removeAttr('style');
                bootbox.dialog({
                    className: "panel-heading",
                    message: html,
                    title: "Thêm menu",
                    buttons: {
                        danger: {
                            label: "Thoát",
                            className: "btn-danger",
                            callback: function () {
                            }
                        },
                        main: {
                            label: '<i class="icon-save"></i> Hoàn tất',
                            className: "btn-primary",
                            callback: function () {
                                var name = html.find('.name').val();
                                var url = html.find('.url').val();
                                var loca = html.find('.loca').val();
                                var sort = html.find('.sort').val();
                                $.ajax({
                                    type: "POST",
                                    url: baseURL + '/backend/template/edit',
                                    cache: false,
                                    dataType: 'json',
                                    data: {
                                        url: url,
                                        name: name,
                                        loca: loca,
                                        action: 'add',
                                        tem_id: tem_id,
                                        sort: sort
                                    },
                                    beforeSend: function () {
                                    },
                                    success: function (result) {
                                        oTable.fnAddData([
                                            '<td><img src="' + STATIS_URL + '/b/images/details_open.png"></td>',
                                            '<td>' + loca + '</td>',
                                            '<td>' + result.keys + '</td>',
                                            '<td>' + name + '</td>',
                                            '<td>' + url + '</td>',
                                            '<td>' + sort + '</td>',
                                            '<td><a class="btn btn-danger remove_menu" data-loca="' + loca + '" data-key="' + result.keys + '"><i class="icon-trash "></i></a></td>'
                                        ], false);
                                    }
                                });

                            }
                        }
                    }
                });
            });
            $(document).on('click', '#table-menu-info tbody td img', function () {
                var nTr = $(this).parents('tr')[0];
                if (oTable.fnIsOpen(nTr))
                {
                    /* This row is already open - close it */
                    this.src = STATIS_URL + '/b/images/details_open.png';
                    oTable.fnClose(nTr);
                }
                else
                {
                    /* Open this row */
                    this.src = STATIS_URL + '/b/images/details_close.png';
                    oTable.fnOpen(nTr, fnFormatDetails(oTable, nTr), 'details');
                }
            });

        });
        function fnFormatDetails(oTable, nTr)
        {
            var aData = oTable.fnGetData(nTr);
            var sOut = '<div class="menu_info"><form action="" method="POST"><div class="form-group"><label class="col-lg-3">Name</label><div class="col-lg-9"><input class="form-control name" type="text" value="' + aData[3] + '" name="name"></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Url</label><div class="col-lg-9"><input class="form-control url" type="text" value="' + aData[4] + '" name="url"></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Sort</label><div class="col-lg-9"><input class="form-control sort" type="text" value="' + aData[5] + '" name="sort"></div></div>';
            sOut += '<div class="form-group col-lg-12"><button data-key="' + aData[2] + '" data-location="' + aData[1] + '" class="save_menu btn btn-success">Lưu</button></div></form>';
            sOut += '</div>';
            return sOut;
        }
    },
    product: function () {
        $(document).ready(function () {
            $(document).on('click', '.load_produc', function () {
                var not_id_produc = $(this).data('bind');
                var _that = $(this).parent().next();
                $.ajax({
                    type: "POST",
                    url: baseURL + '/backend/template/load_product',
                    cache: false,
                    dataType: 'html',
                    data: {
                        not_id_product: not_id_produc
                    },
                    beforeSend: function () {
                        _that.html('<img src="' + STATIS_URL + '/b/images/ajax_loader_blue.gif">');
                    },
                    success: function (result) {
                        _that.html(result)
                    }
                });
            });
            $(document).on('click', '.btn-deleteProductTab', function () {
                var _that = $(this).closest('tr').get(0);
                var _key = $(this).data('key');
                var button = $(this).closest('.tab-pane').find('.load_produc');
                var name_product = pTable[_key].fnGetData(_that)[1];
                var _id = $(this).data('id');
                var append = $('.unListProduc').find('tbody');
                bootbox.confirm('Bạn có muốn xóa sản phẩm này không', function (result1) {
                    if (result1) {
                        $.ajax({
                            type: "POST",
                            url: baseURL + '/backend/template/edit',
                            cache: false,
                            dataType: 'json',
                            data: {
                                key: _key - 1,
                                action: 'delete',
                                tem_id: tem_id,
                                _id: _id,
                                status: 'product'
                            },
                            beforeSend: function () {

                            },
                            success: function (result) {
                                if (!result.error) {
                                    pTable[_key].fnDeleteRow(_that);
                                    if (append != undefined) {
                                        append.prepend('<tr id="' + _id + '"><td style="text-align: center"><a class="btn btn-success btn-xs censor btn-addProduct"  data-id="' + _id + '"><i class="icon-arrow-left"></i> Add</a></td><td>' + _id + '</td><td>' + name_product + '</td></tr>');
                                    }
                                    var data_id = button.data('bind');
                                    var arrData = data_id.split(",");
                                    arrData = $.grep(arrData, function (value) {
                                        return value != _id;
                                    });
                                    var strId = arrData.join(',');
                                    button.data('bind', strId);
                                } else {
                                    bootbox.alert(result.message);
                                }

                            }
                        });

                    }
                });
            });
            $(document).on('click', '.btn-addProduct', function () {
                var _that = $(this).closest('tr');
                var _id = $(this).data('id');
                var button = $(this).closest('.tab-pane').find('.load_produc');
                var _key = $(this).closest('.unListProduc').data('id');
                var prod_name = $(this).parent().next().next().html();
                $.ajax({
                    type: "POST",
                    url: baseURL + '/backend/template/edit',
                    cache: false,
                    dataType: 'json',
                    data: {
                        key: _key - 1,
                        action: 'add',
                        tem_id: tem_id,
                        _id: _id,
                        status: 'product'
                    },
                    beforeSend: function () {
                    },
                    success: function (result) {
                        if (!result.error) {

                            pTable[_key].fnAddData([
                                _id,
                                prod_name,
                                '<a class="btn btn-danger btn-xs btn-deleteProductTab" data-placement="top" data-original-title="Loại bỏ bài viết" data-key="' + _key + '" data-id="' + _id + '">Dele <i class="icon-arrow-right"></i></a>'
                            ]);
                            _that.remove();
                            var data_id = button.data('bind');
                            var data_bind = '';
                            if (data_id === '' || data_id === undefined) {
                                data_bind = _id;
                            } else {
                                data_bind = data_id + ',' + _id;
                            }
                            button.data('bind', data_bind);
                        } else {
                            bootbox.alert(result.message);
                        }

                    }
                });
            });
            $(document).on('click', '.ajaxLoadpage', function () {
                var page = $(this).data('page');
                var unListProduc = $(this).closest('.unListProduc');
                var not_id_product = unListProduc.closest('section.panel').find('button.load_produc').data('bind');
                var keyword = unListProduc.find('.search_product').val();
                $.ajax({
                    type: "POST",
                    url: baseURL + '/backend/template/load_product',
                    cache: false,
                    dataType: 'html',
                    data: {
                        keyword: keyword,
                        not_id_product: not_id_product,
                        page: page,
                    },
                    beforeSend: function () {
                        unListProduc.html('<img src="' + STATIS_URL + '/b/images/ajax_loader_blue.gif">');
                    },
                    success: function (result) {
                        unListProduc.html(result)
                    }
                });
            });
        });

    },
    image: function () {
        $(document).ready(function () {
            /*
             * Insert a 'details' column to the table
             */
            var nCloneTh = document.createElement('th');
            var nCloneTd = document.createElement('td');
            nCloneTd.innerHTML = '<img class="showdetail" src="' + STATIS_URL + '/b/images/details_open.png">';
            nCloneTd.className = "center";

            $('#table-image-info thead tr').each(function () {
                this.insertBefore(nCloneTh, this.childNodes[0]);
            });

            $('#table-image-info tbody tr').each(function () {
                this.insertBefore(nCloneTd.cloneNode(true), this.childNodes[0]);
            });

            /*
             * Initialse DataTables, with no sorting on the 'details' column
             */
            var oTable = $('#table-image-info').dataTable({
                "aoColumnDefs": [
                    {"bSortable": false, "aTargets": [0]}
                ],
//                "bSort": false
                "aaSorting": [[1, 'asc']]
            });

            /* Add event listener for opening and closing details
             * Note that the indicator for showing which row is open is not controlled by DataTables,
             * rather it is done here
             */
            $(document).on('click', '.save_image', function () {
                var _that = $(this).closest('form');
                var _key = $(this).data('key');
                var row = oTable.fnGetPosition($(this).closest('tr').prev().get(0));
                var name = _that.find('.name').val();
                var url = _that.find('.url').val();
                var sort = _that.find('.sort').val();
                $.ajax({
                    type: "POST",
                    url: baseURL + '/backend/template/edit',
                    cache: false,
                    dataType: 'json',
                    data: _that.serialize() + '&tem_id=' + tem_id + '&key=' + _key + '&action=edit&status=image',
                    beforeSend: function () {

                    },
                    success: function (result) {
                        bootbox.alert(result.message);
                        if (!result.error) {
                            oTable.fnUpdate(name, row, 3);
                        oTable.fnUpdate(url, row, 4);
                        oTable.fnUpdate(sort, row, 5);
                        }
                        
//                                   console.log;
                    }
                });
                return false;
            });
            $(document).on('click', '.remove_image', function () {
                var key = $(this).data('key');
//                var _that = $(this).closest('tr').get(0);
                bootbox.confirm("Bạn có muốn xóa image này?", function (result1) {
                    if (result1) {
                        $.ajax({
                            type: "POST",
                            url: baseURL + '/backend/template/edit',
                            cache: false,
                            dataType: 'json',
                            data: {
                                key: key,
                                action: 'delete',
                                status: 'image',
                                tem_id: tem_id
                            },
                            beforeSend: function () {
                            },
                            success: function (result) {
                                bootbox.alert(result.message);
                                if (!result.error) {
                                    setTimeout(function () {
                                        document.location = document.location
                                    }, 1000);
                                }
                            }
                        });
                    }
                });
            });
            $(document).on('click', '.add_image', function () {

                var html = $("#html_add_image_hidden").clone(true).removeAttr('id').removeAttr('style');
                var form = html.find('form');
                var id = 'img';
                html.find('.img').attr('id', id);
                html.find('.progress1').attr('id', 'progress' + id);
                html.find('ul').addClass('listImage' + id);
                bootbox.dialog({
                    className: "panel-heading",
                    message: html,
                    title: "Thêm banner",
                    buttons: {
                        danger: {
                            label: "Thoát",
                            className: "btn-danger",
                            callback: function () {
                            }
                        },
                        main: {
                            label: '<i class="icon-save"></i> Hoàn tất',
                            className: "btn-primary",
                            callback: function () {
                                $.ajax({
                                    type: "POST",
                                    url: baseURL + '/backend/template/edit',
                                    cache: false,
                                    dataType: 'json',
                                    data: form.serialize() + '&tem_id=' + tem_id + '&status=image&action=add',
                                    beforeSend: function () {
                                    },
                                    success: function (result) {
                                        bootbox.alert(result.message);
                                        if (!result.error) {
                                            setTimeout(function () {
                                                document.location = document.location
                                            }, 1000);
                                        }
                                    }
                                });

                            }
                        }
                    }
                });
                Template.uploadImage(id);
            });
            $(document).on('click', '#table-image-info tbody td img.showdetail', function () {
                var nTr = $(this).parents('tr')[0];
                if (oTable.fnIsOpen(nTr))
                {
                    /* This row is already open - close it */
                    this.src = STATIS_URL + '/b/images/details_open.png';
                    oTable.fnClose(nTr);
                }
                else
                {
                    /* Open this row */
                    this.src = STATIS_URL + '/b/images/details_close.png';
                    oTable.fnOpen(nTr, fnFormatDetails(oTable, nTr), 'details');
                    var key = oTable.fnGetData(nTr)[1];
                    Template.uploadImage('img' + key);
                }
            });


            $(document).on('click', ".delete", function () {
                $(this).closest('li').remove();
            });
//            Template.uploadImage('img');
        });
        function fnFormatDetails(oTable, nTr)
        {
            var aData = oTable.fnGetData(nTr);
            var sOut = '<div class="menu_info"><form action="" method="POST">';
            sOut += '<div class="form-group"><label class="col-lg-3">Title</label><div class="col-lg-9"><input class="form-control title" type="text" value="' + aData[3] + '" name="title"></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Url</label><div class="col-lg-9"><input class="form-control url" type="text" value="' + aData[4] + '" name="url"></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Sort</label><div class="col-lg-9"><input class="form-control sort" type="text" value="' + aData[5] + '" name="sort"></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Image</label><div class="col-lg-9"><input type="file"  name="img' + aData[1] + '" id="img' + aData[1] + '"><div id="progressimg' + aData[1] + '">' + aData[2] + '</div><ul class="listImageimg' + aData[1] + '"></ul></div></div>';
            sOut += '<div class="form-group col-lg-12"><button data-key="' + aData[1] + '" class="save_image btn btn-success">Lưu</button></div></form>';
            sOut += '</div>';
            return sOut;
        }
    },
    title: function () {
        $(document).ready(function () {
            if ($('textarea.html_form').length >= 1) {
                tinymce.init({
                    selector: "textarea.html_form",
                    plugins: [
                        "advlist autolink lists link image charmap print preview anchor",
                        "searchreplace visualblocks code fullscreen",
                        "insertdatetime media table contextmenu paste jbimages"
                    ],
                    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image jbimages",
                    relative_urls: false
                });
            }
            $(document).on('click', '.save-title', function () {
                var form = $(this).closest('form');
                if ($('textarea.html_form').length >= 1) {
                    var html_form = tinymce.activeEditor.getContent();
                }else{
                    html_form=null;
                }
                $.ajax({
                    type: "POST",
                    url: baseURL + '/backend/template/edit',
                    cache: false,
                    dataType: 'json',
                    data: form.serialize() + '&tem_id=' + tem_id + '&status=title&html_form=' + html_form,
                    beforeSend: function () {
                    },
                    success: function (result) {
                        bootbox.alert(result.message);

                    }
                });
            });
            $(document).on('click', '.typer-formhtml div', function () {
                $('.typer-formhtml div').removeClass('ac');
                $(this).addClass('ac');
                var form = $(this).data('id');
                tinymce.activeEditor.setContent($('#' + form).html());
            });
        });
    },
    mobileImage: function () {
        $(document).ready(function () {
            /*
             * Insert a 'details' column to the table
             */
            var nCloneTh = document.createElement('th');
            var nCloneTd = document.createElement('td');
            nCloneTd.innerHTML = '<img class="showdetail" src="' + STATIS_URL + '/b/images/details_open.png">';
            nCloneTd.className = "center";

            $('#table-image-info thead tr').each(function () {
                this.insertBefore(nCloneTh, this.childNodes[0]);
            });

            $('#table-image-info tbody tr').each(function () {
                this.insertBefore(nCloneTd.cloneNode(true), this.childNodes[0]);
            });

            /*
             * Initialse DataTables, with no sorting on the 'details' column
             */
            var oTable = $('#table-image-info').dataTable({
                "aoColumnDefs": [
                    {"bSortable": false, "aTargets": [0]}
                ],
//                "bSort": false
                "aaSorting": [[1, 'asc']]
            });

            /* Add event listener for opening and closing details
             * Note that the indicator for showing which row is open is not controlled by DataTables,
             * rather it is done here
             */
            $(document).on('click', '.save_image', function () {
                var _that = $(this).closest('form');
                var _key = $(this).data('key');
                var row = oTable.fnGetPosition($(this).closest('tr').prev().get(0));
                var name = _that.find('.name').val();
                var url = _that.find('.url').val();
                var sort = _that.find('.sort').val();
                $.ajax({
                    type: "POST",
                    url: baseURL + '/backend/template/edit',
                    cache: false,
                    dataType: 'json',
                    data: _that.serialize() + '&tem_id=' + tem_id + '&key=' + _key + '&action=edit&status=image',
                    beforeSend: function () {

                    },
                    success: function (result) {
                        bootbox.alert(result.message);
                        if (!result.error) {
                            oTable.fnUpdate(name, row, 3);
                        oTable.fnUpdate(url, row, 4);
                        oTable.fnUpdate(sort, row, 5);
                        }
                        
//                                   console.log;
                    }
                });
                return false;
            });
            $(document).on('click', '.remove_image', function () {
                var key = $(this).data('key');
//                var _that = $(this).closest('tr').get(0);
                bootbox.confirm("Bạn có muốn xóa image này?", function (result1) {
                    if (result1) {
                        $.ajax({
                            type: "POST",
                            url: baseURL + '/backend/template/edit',
                            cache: false,
                            dataType: 'json',
                            data: {
                                key: key,
                                action: 'delete',
                                status: 'image',
                                tem_id: tem_id
                            },
                            beforeSend: function () {
                            },
                            success: function (result) {
                                bootbox.alert(result.message);
                                if (!result.error) {
                                    setTimeout(function () {
                                        document.location = document.location
                                    }, 1000);
                                }
                            }
                        });
                    }
                });
            });
            $(document).on('click', '.add_image', function () {

                var html = $("#html_add_image_hidden").clone(true).removeAttr('id').removeAttr('style');
                var form = html.find('form');
                var id = 'img';
                html.find('.img').attr('id', id);
                html.find('.progress1').attr('id', 'progress' + id);
                html.find('ul').addClass('listImage' + id);
                bootbox.dialog({
                    className: "panel-heading",
                    message: html,
                    title: "Thêm banner",
                    buttons: {
                        danger: {
                            label: "Thoát",
                            className: "btn-danger",
                            callback: function () {
                            }
                        },
                        main: {
                            label: '<i class="icon-save"></i> Hoàn tất',
                            className: "btn-primary",
                            callback: function () {
                                $.ajax({
                                    type: "POST",
                                    url: baseURL + '/backend/template/edit',
                                    cache: false,
                                    dataType: 'json',
                                    data: form.serialize() + '&tem_id=' + tem_id + '&status=image&action=add',
                                    beforeSend: function () {
                                    },
                                    success: function (result) {
                                        bootbox.alert(result.message);
                                        if (!result.error) {
                                            setTimeout(function () {
                                                document.location = document.location
                                            }, 1000);
                                        }
                                    }
                                });

                            }
                        }
                    }
                });
                Template.uploadImage(id);
            });
            $(document).on('click', '#table-image-info tbody td img.showdetail', function () {
                var nTr = $(this).parents('tr')[0];
                if (oTable.fnIsOpen(nTr))
                {
                    /* This row is already open - close it */
                    this.src = STATIS_URL + '/b/images/details_open.png';
                    oTable.fnClose(nTr);
                }
                else
                {
                    /* Open this row */
                    this.src = STATIS_URL + '/b/images/details_close.png';
                    oTable.fnOpen(nTr, fnFormatDetails(oTable, nTr), 'details');
                    var key = oTable.fnGetData(nTr)[1];
                    Template.uploadImage('img' + key);
                }
            });


            $(document).on('click', ".delete", function () {
                $(this).closest('li').remove();
            });
//            Template.uploadImage('img');
        });
        function fnFormatDetails(oTable, nTr)
        {
            var aData = oTable.fnGetData(nTr);
            var sOut = '<div class="menu_info"><form action="" method="POST">';
            sOut += '<div class="form-group"><label class="col-lg-3">Title</label><div class="col-lg-9"><input class="form-control title" type="text" value="' + aData[3] + '" name="title"></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Url</label><div class="col-lg-9"><input class="form-control url" type="text" value="' + aData[4] + '" name="url"></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Sort</label><div class="col-lg-9"><input class="form-control sort" type="text" value="' + aData[5] + '" name="sort"></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Image</label><div class="col-lg-9"><input type="file"  name="img' + aData[1] + '" id="img' + aData[1] + '"><div id="progressimg' + aData[1] + '">' + aData[2] + '</div><ul class="listImageimg' + aData[1] + '"></ul></div></div>';
            sOut += '<div class="form-group col-lg-12"><button data-key="' + aData[1] + '" class="save_image btn btn-success">Lưu</button></div></form>';
            sOut += '</div>';
            return sOut;
        }
    },
    mobileTitle: function () {
        $(document).ready(function () {
            $(document).on('click', '.save-title', function () {
                var form = $(this).closest('form');
                $.ajax({
                    type: "POST",
                    url: baseURL + '/backend/template/edit',
                    cache: false,
                    dataType: 'json',
                    data: form.serialize() + '&tem_id=' + tem_id + '&status=title',
                    beforeSend: function () {
                    },
                    success: function (result) {
                        bootbox.alert(result.message);

                    }
                });
            });
            
        });
    },
    uploadImage: function (id) {
        $(document).ready(function () {
            $("#" + id).Nileupload({
                action: baseurl + '/backend/uploader/upload_content?folder=banners&filename=' + id,
                size: '3MB',
                extension: 'jpg,jpeg,png,gif',
                progress: $("#progress" + id),
                preview: $(".listImage" + id),
                multi: false
            });
        });
    },
    tem1Menu: function () {
        $(document).ready(function () {
            var nCloneTh = document.createElement('th');
            var nCloneTd = document.createElement('td');
            nCloneTd.innerHTML = '<img src="' + STATIS_URL + '/b/images/details_open.png">';
            nCloneTd.className = "center";

            $('#table-menu-info thead tr').each(function () {
                this.insertBefore(nCloneTh, this.childNodes[0]);
            });

            $('#table-menu-info tbody tr').each(function () {
                this.insertBefore(nCloneTd.cloneNode(true), this.childNodes[0]);
            });

            /*
             * Initialse DataTables, with no sorting on the 'details' column
             */
            var oTable = $('#table-menu-info').dataTable({
                "aoColumnDefs": [
                    {"bSortable": false, "aTargets": [0]}
                ],
//                "bSort": false
                "aaSorting": [[1, 'asc']]
            });

            $(document).on('click', '.save_menu', function () {
                var _that = $(this).closest('form');
                var _key = $(this).data('key');
                var row = oTable.fnGetPosition($(this).closest('tr').prev().get(0));
                var title = _that.find('.title').val();
                var url = _that.find('.url').val();
                var sort = _that.find('.sort').val();
                $.ajax({
                    type: "POST",
                    url: baseURL + '/backend/template/edit',
                    cache: false,
                    dataType: 'json',
                    data: _that.serialize() + '&tem_id=' + tem_id + '&key=' + _key + '&action=edit&status=menutop',
                    beforeSend: function () {

                    },
                    success: function (result) {
                        oTable.fnUpdate(title, row, 2);
                        oTable.fnUpdate(url, row, 3);
//                      oTable.fnUpdate(sort, row, 5);
//                                   console.log;
                    }
                });
                return false;
            });
            $(document).on('click', '.remove_menu', function () {
                var key = $(this).data('key');
                var _that = $(this).closest('tr').get(0);
                bootbox.confirm("Bạn có muốn xóa menu này?", function (result1) {
                    if (result1) {
                        $.ajax({
                            type: "POST",
                            url: baseURL + '/backend/template/edit',
                            cache: false,
                            dataType: 'json',
                            data: {
                                key: key,
                                status: 'menutop',
                                action: 'delete',
                                tem_id: tem_id
                            },
                            beforeSend: function () {
                            },
                            success: function (result) {
                                oTable.fnDeleteRow(oTable.fnGetPosition(_that));
                            }
                        });
                    }
                });
            });
            $(document).on('click', '.add_menu', function () {
                var html = $("#html_add_menu_top").clone(true).removeAttr('id').removeAttr('style');
                bootbox.dialog({
                    className: "panel-heading",
                    message: html,
                    title: "Thêm menu",
                    buttons: {
                        danger: {
                            label: "Thoát",
                            className: "btn-danger",
                            callback: function () {
                            }
                        },
                        main: {
                            label: '<i class="icon-save"></i> Hoàn tất',
                            className: "btn-primary",
                            callback: function () {
                                var title = html.find('.title').val();
                                var url = html.find('.url').val();
                                $.ajax({
                                    type: "POST",
                                    url: baseURL + '/backend/template/edit',
                                    cache: false,
                                    dataType: 'json',
                                    data: {
                                        url: url,
                                        title: title,
                                        status: 'menutop',
                                        action: 'add',
                                        tem_id: tem_id,
                                    },
                                    beforeSend: function () {
                                    },
                                    success: function (result) {
                                        oTable.fnAddData([
                                            '<img src="' + STATIS_URL + '/b/images/details_open.png">',
                                            result.keys,
                                            title,
                                            url,
                                            '<a class="btn btn-danger remove_menu" data-key="' + result.keys + '"><i class="icon-trash "></i></a>'
                                        ]);
                                    }
                                });

                            }
                        }
                    }
                });
            });
            $(document).on('click', '#table-menu-info tbody td img', function () {
                var nTr = $(this).parents('tr')[0];
                if (oTable.fnIsOpen(nTr))
                {
                    /* This row is already open - close it */
                    this.src = STATIS_URL + '/b/images/details_open.png';
                    oTable.fnClose(nTr);
                }
                else
                {
                    /* Open this row */
                    this.src = STATIS_URL + '/b/images/details_close.png';
                    oTable.fnOpen(nTr, fnFormatDetails(oTable, nTr), 'details');
                }
            });

        });
        function fnFormatDetails(oTable, nTr)
        {
            var aData = oTable.fnGetData(nTr);
            var sOut = '<div class="menu_info"><form action="" method="POST"><div class="form-group"><label class="col-lg-3">Title</label><div class="col-lg-9"><input class="form-control title" name="title" type="text" value="' + aData[2] + '"></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Url</label><div class="col-lg-9"><input class="form-control url" type="text" value="' + aData[3] + '" name="url"></div></div>';
            sOut += '<div class="form-group col-lg-12"><button data-key="' + aData[1] + '" class="save_menu btn btn-success">Lưu</button></div></form>';
            sOut += '</div>';
            return sOut;
        }
    },
    tem1LeftMenu: function (_keyL) {
        $(document).ready(function () {
             mTable[_keyL] = $('#table-menu-info_'+_keyL).dataTable({
                "aoColumnDefs": [
                    {"bSortable": false, "aTargets": [0]}
                ],
                "bSort": false,
//                "aaSorting": [[1, 'asc']]
            });

            $(document).on('click', '.save_menu_sub_tem1'+_keyL, function () {
               
                var _that = $(this).closest('form');
                var _key = $(this).data('key');
                var parent = $(this).data('parent');
                var row = mTable[_keyL].fnGetPosition($(this).closest('tr').prev().get(0));
                var title1 = _that.find('.title').val();
                if(parent != null){
                    title = '&rdsh;&nbsp;&nbsp;&nbsp;&nbsp;' + title1;
                }else{
                    title = '<strong>' + title1 + '</strong>';
                }
                var url = _that.find('.url').val();
                $.ajax({
                    type: "POST",
                    url: baseURL + '/backend/template/edit',
                    cache: false,
                    dataType: 'json',
                    data: _that.serialize() + '&tem_id=' + tem_id + '&key=' + _key + '&action=edit&status=menuleft_tem1&_keyL='+_keyL+'&parent='+parent,
                    beforeSend: function () {

                    },
                    success: function (result) {
                        mTable[_keyL].fnUpdate(title, row, 2);
                        mTable[_keyL].fnUpdate(url, row, 3);
                        _that.closest('tr').prev().find('img').data('bind',title1);
//                      oTable.fnUpdate(sort, row, 5);
//                                   console.log;
                    }
                });
                return false;
            });
            $(document).on('click', '.remove_menu_temp1'+_keyL, function () {
                var key = $(this).data('key');
                var parent = $(this).data('parent');
                var _that = $(this).closest('tr').get(0);
                bootbox.confirm("Bạn có muốn xóa menu này?", function (result1) {
                    if (result1) {
                        $.ajax({
                            type: "POST",
                            url: baseURL + '/backend/template/edit',
                            cache: false,
                            dataType: 'json',
                            data: {
                                key: key,
                                _keyL: _keyL,
                                parent: parent,
                                status: 'menuleft_tem1',
                                action: 'delete',
                                tem_id: tem_id
                            },
                            beforeSend: function () {
                            },
                            success: function (result) {
                                mTable[_keyL].fnDeleteRow(mTable[_keyL].fnGetPosition(_that));
                            }
                        });
                    }
                });
            });
            $(document).on('click', '.add_menu_temp1'+_keyL, function () {
                var html = $("#html_add_menu_left_tem1"+_keyL).clone(true).removeAttr('id').removeAttr('style');
                bootbox.dialog({
                    className: "panel-heading",
                    message: html,
                    title: "Thêm menu",
                    buttons: {
                        danger: {
                            label: "Thoát",
                            className: "btn-danger",
                            callback: function () {
                            }
                        },
                        main: {
                            label: '<i class="icon-save"></i> Hoàn tất',
                            className: "btn-primary",
                            callback: function () {
                                var title = html.find('.title').val();
                                var url = html.find('.url').val();
                                var parent =html.find('.parent').val();
                                $.ajax({
                                    type: "POST",
                                    url: baseURL + '/backend/template/edit',
                                    cache: false,
                                    dataType: 'json',
                                    data: {
                                        url: url,
                                        _keyL:_keyL,
                                        parent: parent,
                                        title: title,
                                        status: 'menuleft_tem1',
                                        action: 'add',
                                        tem_id: tem_id,
                                    },
                                    beforeSend: function () {
                                    },
                                    success: function (result) {
                                        if(result.error){
                                           bootbox.alert(result.messages);
                                        }else{
                                           document.location = document.location;
                                        }
                                    }
                                });

                            }
                        }
                    }
                });
            });
            $(document).on('click', '#table-menu-info_'+_keyL+' tbody td img', function () {
                var nTr = $(this).parents('tr')[0];
                if (mTable[_keyL].fnIsOpen(nTr))
                {
                    /* This row is already open - close it */
                    this.src = STATIS_URL + '/b/images/details_open.png';
                    mTable[_keyL].fnClose(nTr);
                }
                else
                {
                    /* Open this row */
                    this.src = STATIS_URL + '/b/images/details_close.png';
                    var title = $(this).data('bind');
                    var parent= $(this).data('parent');
                   mTable[_keyL].fnOpen(nTr, fnFormatDetails(mTable[_keyL], nTr,_keyL,title,parent), 'details');
                }
            });
            $(document).on('click', '.save-title-temp1'+_keyL, function () {
                var form = $(this).closest('form');
                $.ajax({
                    type: "POST",
                    url: baseURL + '/backend/template/edit',
                    cache: false,
                    dataType: 'json',
                    data: form.serialize() + '&tem_id=' + tem_id + '&status=menuleft_tem1&action=title&_keyL='+_keyL,
                    beforeSend: function () {
                    },
                    success: function (result) {
                        bootbox.alert(result.message);

                    }
                });
            });

      
        function fnFormatDetails(mT, nTr,_keyL,title,parent){
            var aData = mT.fnGetData(nTr);
            var sOut = '<div class="menu_info"><form action="" method="POST"><div class="form-group"><label class="col-lg-3">Title</label><div class="col-lg-9"><input class="form-control title" name="title" type="text" value="' + title + '"></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Url</label><div class="col-lg-9"><input class="form-control url" type="text" value="' + aData[3] + '" name="url"></div></div>';
            sOut += '<div class="form-group col-lg-12"><button  data-parent="'+parent+'" data-key="' + aData[1] + '" class="save_menu_sub_tem1'+_keyL+' btn btn-success">Lưu</button></div></form>';
            sOut += '</div>';
            return sOut;
        }});
    },
    tem1LeftBanner: function (_keyL) {
        $(document).ready(function () {
            /*
             * Insert a 'details' column to the table
             */
            var nCloneTh = document.createElement('th');
            var nCloneTd = document.createElement('td');
            nCloneTd.innerHTML = '<img class="showdetail" src="' + STATIS_URL + '/b/images/details_open.png">';
            nCloneTd.className = "center";

            $('#table_image_info_'+_keyL+' thead tr').each(function () {
                this.insertBefore(nCloneTh, this.childNodes[0]);
            });

            $('#table_image_info_'+_keyL+' tbody tr').each(function () {
                this.insertBefore(nCloneTd.cloneNode(true), this.childNodes[0]);
            });

            /*
             * Initialse DataTables, with no sorting on the 'details' column
             */
            bTable[_keyL] = $('#table_image_info_'+_keyL).dataTable({
                "aoColumnDefs": [
                    {"bSortable": false, "aTargets": [0]}
                ],
//                "bSort": false
                "aaSorting": [[1, 'asc']]
            });

            /* Add event listener for opening and closing details
             * Note that the indicator for showing which row is open is not controlled by DataTables,
             * rather it is done here
             */
            $(document).on('click', '.save_image_temp1_'+_keyL, function () {
                var _that = $(this).closest('form');
                var _key = $(this).data('key');
                var row = bTable[_keyL].fnGetPosition($(this).closest('tr').prev().get(0));
                var title = _that.find('.title').val();
                var url = _that.find('.url').val();
                var sort = _that.find('.sort').val();
                var img=_that.find('img.image').clone(true).attr('src');
                $.ajax({
                    type: "POST",
                    url: baseURL + '/backend/template/edit',
                    cache: false,
                    dataType: 'json',
                    data: _that.serialize() + '&tem_id=' + tem_id + '&key=' + _key + '&action=edit&status=banner_tem1&_keyL='+_keyL,
                    beforeSend: function () {

                    },
                    success: function (result) {
                        bootbox.alert(result.message);
                        if (!result.error) {
                            if( img != '' && img != undefined){
                                bTable[_keyL].fnUpdate('<img src="'+img+'">', row, 2);
                            }
                            bTable[_keyL].fnUpdate(title, row, 3);
                            bTable[_keyL].fnUpdate(url, row, 4);
                            bTable[_keyL].fnUpdate(sort, row, 5);
                        }
                    }
                });
                return false;
            });
            $(document).on('click', '.remove_image_temp1'+_keyL, function () {
                var key = $(this).data('key');
                var _that = $(this).closest('tr').get(0);
                bootbox.confirm("Bạn có muốn xóa image này?", function (result1) {
                    if (result1) {
                        $.ajax({
                            type: "POST",
                            url: baseURL + '/backend/template/edit',
                            cache: false,
                            dataType: 'json',
                            data: {
                                key: key,
                                _keyL: _keyL,
                                action: 'delete',
                                status: 'banner_tem1',
                                tem_id: tem_id
                            },
                            beforeSend: function () {
                            },
                            success: function (result) {
                                if (!result.error) {
                                    bTable[_keyL].fnDeleteRow(bTable[_keyL].fnGetPosition(_that));
                                }else{
                                     bootbox.alert(result.message);
                                }
                            }
                        });
                    }
                });
            });
            $(document).on('click', '.add_image_temp1'+_keyL, function () {

                var html = $("#html_add_image_hidden").clone(true).removeAttr('id').removeAttr('style');
                var form = html.find('form');
                var id = 'img';
                html.find('.img').attr('id', id);
                html.find('.progress1').attr('id', 'progress' + id);
                html.find('ul').addClass('listImage' + id);
                bootbox.dialog({
                    className: "panel-heading",
                    message: html,
                    title: "Thêm banner",
                    buttons: {
                        danger: {
                            label: "Thoát",
                            className: "btn-danger",
                            callback: function () {
                            }
                        },
                        main: {
                            label: '<i class="icon-save"></i> Hoàn tất',
                            className: "btn-primary",
                            callback: function () {
                                $.ajax({
                                    type: "POST",
                                    url: baseURL + '/backend/template/edit',
                                    cache: false,
                                    dataType: 'json',
                                    data: form.serialize() + '&tem_id=' + tem_id + '&status=banner_tem1&action=add&_keyL='+_keyL,
                                    beforeSend: function () {
                                    },
                                    success: function (result) {
                                        bootbox.alert(result.message);
                                        if (!result.error) {
                                            setTimeout(function () {
                                                document.location = document.location
                                            }, 1000);
                                        }
                                    }
                                });

                            }
                        }
                    }
                });
                Template.uploadImage(id);
            });
            $(document).on('click', '#table_image_info_'+_keyL+' tbody td img.showdetail', function () {
                var nTr = $(this).parents('tr')[0];
                if (bTable[_keyL].fnIsOpen(nTr))
                {
                    /* This row is already open - close it */
                    this.src = STATIS_URL + '/b/images/details_open.png';
                    bTable[_keyL].fnClose(nTr);
                }
                else
                {
                    /* Open this row */
                    this.src = STATIS_URL + '/b/images/details_close.png';
                    bTable[_keyL].fnOpen(nTr, fnFormatDetails(bTable[_keyL], nTr,_keyL), 'details');
                    var key = bTable[_keyL].fnGetData(nTr)[1];
                    Template.uploadImage('img' + _keyL +'_'+key);
                }
            });


            $(document).on('click', ".delete", function () {
                $(this).closest('li').remove();
            });
//            Template.uploadImage('img');
        });
        function fnFormatDetails(oTable, nTr)
        {
            var aData = oTable.fnGetData(nTr);
            var sOut = '<div class="menu_info"><form action="" method="POST">';
            sOut += '<div class="form-group"><label class="col-lg-3">Title</label><div class="col-lg-9"><input class="form-control title" type="text" value="' + aData[3] + '" name="title"></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Url</label><div class="col-lg-9"><input class="form-control url" type="text" value="' + aData[4] + '" name="url"></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Sort</label><div class="col-lg-9"><input class="form-control sort" type="text" value="' + aData[5] + '" name="sort"></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Image</label><div class="col-lg-9"><input type="file"  name="img' + _keyL + '_' + aData[1] + '" id="img' + _keyL +'_' + aData[1] + '"><div id="progressimg' +_keyL+'_' + aData[1] + '">' + aData[2] + '</div><ul class="listImageimg' + _keyL+'_' + aData[1] + '"></ul></div></div>';
            sOut += '<div class="form-group col-lg-12"><button  data-key="' + aData[1] + '" class="save_image_temp1_'+_keyL+' btn btn-success">Lưu</button></div></form>';
            sOut += '</div>';
            return sOut;
        }
    },
    tem1Default: function () {
        $(document).ready(function () {
            var nCloneTh = document.createElement('th');
            var nCloneTd = document.createElement('td');
            nCloneTd.innerHTML = '<img class="showdetail" src="' + STATIS_URL + '/b/images/details_open.png">';
            nCloneTd.className = "center";

            $('#table-image-info thead tr').each(function () {
                this.insertBefore(nCloneTh, this.childNodes[0]);
            });

            $('#table-image-info tbody tr').each(function () {
                this.insertBefore(nCloneTd.cloneNode(true), this.childNodes[0]);
            });

            /*
             * Initialse DataTables, with no sorting on the 'details' column
             */
            var oTable = $('#table-image-info').dataTable({
                "aoColumnDefs": [
                    {"bSortable": false, "aTargets": [0]}
                ],
//                "bSort": false
                "aaSorting": [[1, 'asc']]
            });

            /* Add event listener for opening and closing details
             * Note that the indicator for showing which row is open is not controlled by DataTables,
             * rather it is done here
             */
            $(document).on('click', '.save_image', function () {
                var _that = $(this).closest('form');
                var _key = $(this).data('key');
                var row = oTable.fnGetPosition($(this).closest('tr').prev().get(0));
                var title = _that.find('.title').val();
                var url = _that.find('.url').val();
                var sort = _that.find('.sort').val();
                var summary = _that.find('.summary').val();
                var img=_that.find('img.image').clone(true).attr('src');
                 $.ajax({
                    type: "POST",
                    url: baseURL + '/backend/template/edit',
                    cache: false,
                    dataType: 'json',
                    data: _that.serialize() + '&tem_id=' + tem_id + '&key=' + _key + '&action=edit&status=default',
                    beforeSend: function () {

                    },
                    success: function (result) {
                        if (result.error) {
                              bootbox.alert(result.message);
                        }
                        if( img != '' && img != undefined){
                            oTable.fnUpdate('<img src="'+img+'">', row, 2);
                        }
                        oTable.fnUpdate(title, row, 3);
                        oTable.fnUpdate(url, row, 4);
                        oTable.fnUpdate(sort, row, 5);
                        oTable.fnUpdate(summary, row, 6);
//                                   console.log;
                    }
                });
                return false;
            });
            $(document).on('click', '#table-image-info tbody td img.showdetail', function () {
                var nTr = $(this).parents('tr')[0];
                if (oTable.fnIsOpen(nTr))
                {
                    /* This row is already open - close it */
                    this.src = STATIS_URL + '/b/images/details_open.png';
                    oTable.fnClose(nTr);
                }
                else
                {
                    /* Open this row */
                    this.src = STATIS_URL + '/b/images/details_close.png';
                    oTable.fnOpen(nTr, fnFormatDetails(oTable, nTr), 'details');
                    var key = oTable.fnGetData(nTr)[1];
                    Template.uploadImage('img' + key);
                }
            });


            $(document).on('click', ".delete", function () {
                $(this).closest('li').remove();
            });
//            Template.uploadImage('img');
        });
        function fnFormatDetails(oTable, nTr)
        {
            var aData = oTable.fnGetData(nTr);
            var sOut = '<div class="menu_info"><form action="" method="POST">';
            sOut += '<div class="form-group"><label class="col-lg-3">Title</label><div class="col-lg-9"><input class="form-control title" type="text" value="' + aData[3] + '" name="title"></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Url</label><div class="col-lg-9"><input class="form-control url" type="text" value="' + aData[4] + '" name="url"></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Sort</label><div class="col-lg-9"><input class="form-control sort" type="text" value="' + aData[5] + '" name="sort"></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Summary</label><div class="col-lg-9"> <textarea class="form-control summary"name="summary" rows="2" cols="50">' + aData[6] + '</textarea></div></div>';
            sOut += '<div class="form-group"><label class="col-lg-3">Image</label><div class="col-lg-9"><input type="file"  name="img' + aData[1] + '" id="img' + aData[1] + '"><div id="progressimg' + aData[1] + '">' + aData[2] + '</div><ul class="listImageimg' + aData[1] + '"></ul></div></div>';
            sOut += '<div class="form-group col-lg-12"><button data-key="' + aData[1] + '" class="save_image btn btn-success">Lưu</button></div></form>';
            sOut += '</div>';
            return sOut;
        }
    },
}
