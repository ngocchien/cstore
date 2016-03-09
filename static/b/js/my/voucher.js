var Voucher = {
    index: function () {
        Voucher.date();
        Voucher.submitClose();
        Voucher.getTypeVoucher();
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
            setTimeout(function () {
                $("#main-from").val($('.dpd1').val());
            }, 300)

        }).data('datepicker');
        var checkout = $('.dpd2').datepicker({
            onRender: function (date) {
                return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
            }
        }).on('changeDate', function (ev) {
            checkout.hide();
            setTimeout(function () {
                $("#main-to").val($('.dpd2').val());
            }, 300)
        }).data('datepicker');
    },
    getTypeVoucher: function(){
        $('#vouc_type').change(function(){
            var type = $('select[name=vouc_type]').val();
            console.log(type);
            if(type == 1){
                $('#money').val(0);
                $('.money').fadeOut();
            }else{
                $('.money').fadeIn();
            }            
        })    
    }
}

