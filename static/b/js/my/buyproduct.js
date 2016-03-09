var BuyProduct = {
    index: function () {
        $(document).ready(function () {
            BuyProduct.viewLink();
            BuyProduct.sendBuyProd();
            BuyProduct.getLink();
            BuyProduct.viewLogs();
            BuyProduct.date();
        });
    },
    date: function () {
        var checkin = $('.dpd1').datepicker({
            onRender: function (date) {
                return date.valueOf() < now.valueOf() ? 'disabled' : '';
            }
        }).on('changeDate', function (ev) {
            if (ev.date.valueOf() > checkout.date.valueOf()) {
                var newDate = new Date(ev.date)
                newDate.setDate(newDate.getDate() + 1);
                checkout.setValue(newDate);
            }
            checkin.hide();
            $('.dpd2')[0].focus();
        }).data('datepicker');
        var checkout = $('.dpd2').datepicker({
            onRender: function (date) {
                return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
            }
        }).on('changeDate', function (ev) {
            checkout.hide();
        }).data('datepicker');
    },
    viewLink: function () {
        $('.view-link').click(function () {
            var prodID = $(this).attr('rel');
            var prodName = $('.cf tr#' + prodID + ' td').eq(1).find('> a').text();
            //console.log(prodName);
            $.ajax({
                type: "POST",
                url: baseurl + '/backend/buyProduct/getLinkProduct',
                dataType: 'json',
                cache: false,
                data: {
                    'prod_id': prodID,
                },
                success: function (result) {
                    if (result.success == 1) {
                        var link = '';
                        if (result.link == 1) {
                            $.each(result.data, function (i, k) {
                                if(k != null){
                                    link += k;
                                
                                if((i+1) < result.data.length){
                                    link += "\n";
                                }}
                            });
                        }
                        bootbox.dialog({
                            message: '<table style="margin-bottom: 5px">\n\
                                            <tr><td><b>Tên sản phẩm: </b></td>\n\
                                            <td>' + prodName + '</td>\n\
                                         </tr></table>\n\
                                         <textarea rows="10" name="link" id="link" class="form-control">' + link + '</textarea>',
                            title: "Link mua hàng!",
                            buttons: {
                                danger: {
                                    label: "Thoát",
                                    className: "btn-danger",
                                    callback: function () {

                                    }
                                },
                                success: {
                                    label: "Xác nhận",
                                    className: "btn-success",
                                    callback: function () {
                                        var link = $('#link').val();
                                        console.log(link);
                                        $.ajax({
                                            type: "POST",
                                            url: baseurl + '/backend/buyProduct/editLink',
                                            dataType: 'json',
                                            cache: false,
                                            data: {
                                                'link': link,
                                                'prod_name': prodName,
                                                'prod_id': prodID,
                                            },
                                            success: function (result) {
                                                if (result.success == 1) {
                                                    bootbox.alert('<b>Chỉnh sửa link thành công</b>', function () {
                                                        window.parent.location = window.parent.location;
                                                    });
                                                } else {
                                                    bootbox.alert('<b>'+ result.ms + '</b>');
                                                    return false;
                                                }
                                            }
                                        });
                                    }
                                }
                            }
                        });
                    } else {
                        bootbox.alert('<b> xảy ra lỗi </b>');
                        return false;
                    }
                }
            });
        });
    },
    sendBuyProd: function () {
        $('.send-prod').click(function () {
            var id = $(this).data("id")
            var prodID = $(this).attr('rel');
            var prodName = $('.cf tr#' + prodID + ' td').eq(1).find('> a').text();
            var strProdCode = $('.cf tr#' + prodID + ' td').eq(0).text();
            //console.log(prodName);
            $.ajax({
                type: "POST",
                url: baseurl + '/backend/buyProduct/getLinkProduct',
                dataType: 'json',
                cache: false,
                data: {
                    'prod_id': prodID,
                },
                success: function (result) {
                    if (result.success == 1) {
                        var link = 'Chưa có link mua cho sản phẩm này';
                        if (result.link == 1) {
                            link = '';
                            $.each(result.data, function (i, k) {
                                var temp = '<div class="radio">\n\
                                                <lable>\n\
                                                    <input type="radio" name="link" value="'+ k +'"><a href="' + k + '" target="_bank">' + k + '<a>\n\
                                                </lable>\n\
                                            </div>';
                                link += temp;
                            });
                        }
                        bootbox.dialog({
                            message: '<div class="row" style="margin-bottom: 5px;">\n\
                                            <label for="cont_slug" class="col-lg-3 col-md-2 col-sm-2 control-label" value="">Tên sản phẩm</label>\n\
                                            <div class="col-lg-6">\n\
                                                <input readonly type="text" value="' + prodName + '" class="form-control" id="user_email">\n\
                                            </div>\n\
                                        </div>\n\
                                        <div class="row" style="margin-bottom: 5px;">\n\
                                            <label for="cont_slug" class="col-lg-3 col-md-2 col-sm-2 control-label" value="">Số lượng</label>\n\
                                            <div class="col-lg-6">\n\
                                                <input type="text" class="form-control" id="quantity">\n\
                                            </div>\n\
                                        </div>\n\
                                        <div class="row" style="margin-bottom: 5px;">\n\
                                            <label for="cont_slug" class="col-lg-3 col-md-2 col-sm-2 control-label" value="">Email</label>\n\
                                            <div class="col-lg-6">\n\
                                                <input type="text" class="form-control" id="email">\n\
                                            </div>\n\
                                        </div>\n\
                                        <div class="row" style="margin-bottom: 5px;">\n\
                                            <label for="comm_content" class="col-sm-4 control-label">Các link mua sản phẩm:</label>\n\
                                            <div class="col-sm-12" style="margin-left: 20px">'
                                    + link + '</div>\n\
                                        </div>',
                            title: "Mua sản phảm",
                            buttons: {
                                danger: {
                                    label: "Thoát",
                                    className: "btn-danger",
                                    callback: function () {

                                    }
                                },
                                success: {
                                    label: "Gửi",
                                    className: "btn-success",
                                    callback: function () {
                                        var quantity = $('#quantity').val();
                                        var email = $('#email').val();
                                        var link = $("input[name='link']:checked").val();
                                        if (link != null){
                                            $.ajax({
                                                type: "POST",
                                                url: baseurl + '/backend/buyProduct/send-mail',
                                                dataType: 'json',
                                                cache: false,
                                                data: {
                                                    'id': id,
                                                    'link': link,
                                                    'prod_name': prodName,
                                                    'prod_id': prodID,
                                                    'quantity': quantity,
                                                    'email': email,
                                                    'prod_code': strProdCode,
                                                },
                                                beforeSend: function(){
                                                    bootbox.alert('<b>Đang gửi mail...</b>');
                                                },
                                                success: function (result) {
                                                    bootbox.hideAll();
                                                    if (result.success == 1) {
                                                        bootbox.alert('<b>Gửi yêu cầu thành công</b>', function () {
                                                            window.location = window.parent.location.pathname;
                                                        });
                                                    } else {
                                                        bootbox.alert('<b>' + result.ms +'</b>');
                                                        return false;
                                                    }
                                                }
                                            });
                                        }
                                        else{
                                            bootbox.alert('<b>Sản phẩm này chưa có link, vui lòng chọn link</b>');
                                            return false;
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        bootbox.alert('<b> xảy ra lỗi </b>');
                        return false;
                    }
                }
            });
        });
    },
    getLink: function () {
        $('.link-buy').click(function () {
            var prodID = $(this).attr('rel');
            var prodName = $('.cf tr#' + prodID + ' td').eq(1).find('> a').text();
            //console.log(prodName);
            $.ajax({
                type: "POST",
                url: baseurl + '/backend/buyProduct/getLinkProduct',
                dataType: 'json',
                cache: false,
                data: {
                    'prod_id': prodID,
                },
                success: function (result) {
                    if (result.success == 1) {
                        var link = '';
                        bootbox.dialog({
                            message: '<table style="margin-bottom: 5px">\n\
                                            <tr><td><b>Tên sản phẩm: </b></td>\n\
                                            <td>' + prodName + '</td>\n\
                                         </tr></table>\n\
                                         <textarea rows="10" name="link" id="link" class="form-control">' + result.data['link_buy'] + '</textarea>',
                            title: "Link mua hàng!",
                            buttons: {
                                success: {
                                    label: "OK",
                                    className: "bbtn-success",
                                    callback: function () {
                                        window.location = window.location.href;
                                    }
                                },
                            }
                        });
                    } else {
                        bootbox.alert('<b> xảy ra lỗi </b>');
                        return false;
                    }
                }
            })
        });
    },
    viewLogs: function () {
        $('.view-logs').click(function () {
            var prodID = $(this).attr('rel');
            var prodName = $('.cf tr#' + prodID + ' td').eq(1).find('> a').text();
            console.log("prodName");
            $.ajax({
                type: "POST",
                url: baseurl + '/backend/buyProduct/getLogs',
                dataType: 'json',
                cache: false,
                data: {
                    'prod_id': prodID,
                },
                success: function (result) {
                    if (result.success == 1) {
                        if (result.logs == 1) {
                            var logs = '';
                            $.each(result.data, function (i, k) {
                                var temp = '<tr>\n\
                                                <td class="text-center" width="100">' + k['date'] + '</td>\n\
                                                <td class="text-center" width="100">' + k['quantity'] + '</td>\n\
                                                <td class="text-center" width="400"><a href="' + k['link'] + '" target="_bank">Link</a></td>\n\
                                            </tr>';
                                logs += temp;
                            });
                            bootbox.dialog({
                                message: '<table style="margin-bottom: 5px">\n\
                                         <tr>\n\
                                            <td><b>Tên sản phẩm: </b></td>\n\
                                            <td>' + prodName + '</td>\n\
                                         </tr></table>\n\
                                        <table class="table table-bordered table-striped table-condensed cf>\n\
                                            <thead class="cf">\n\
                                                <tr>\n\
                                                    <th class="numeric text-center">Ngày mua</th>\n\
                                                    <th class="numeric text-center">Số lượng</th>\n\
                                                    <th class="numeric text-center">Link mua</th>\n\
                                                </tr>\n\
                                            </thead>\n\
                                            <tbody>' 
                                                + logs +
                                            '</tbody>\n\
                                        </table>',
                                title: "Lịch sử mua sản phẩm!",
                                buttons: {
                                    success: {
                                        label: "Thoát",
                                        className: "btn-success",
                                        callback: function () {

                                        }
                                    },
                                }
                            });
                        }
                        else{
                            bootbox.alert('<b>' + result.ms + '</b>');
                            return false;
                        }
                    } else {
                        bootbox.alert('<b>' + result.ms + '</b>');
                        return false;
                    }
                }
            });
        });
    },
}


