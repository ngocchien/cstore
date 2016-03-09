$(document).ready(function(){
//    if(GROUPID == 1 ){
//        $.ajax({
//            type : 'POST',
//            url : baseurl + '/backend/message/get-note-purchase',
//            cache: false,
//            dataType : 'json',
//            success : function(reuslt){
//                if(reuslt.st==1){
//                    $('#header_notification_bar').show();
//                    $('#header_notification_bar .bg-warning').text(reuslt.intTotal);
//                    $('.notification .yellow').text('Có '+reuslt.intTotal+' sản phẩm cần phải mua gấp');
//                }
//            },
//        });
//    }
    $.ajax({
        type: "POST",
        url: baseurl + '/backend/message/get-all-message',
        cache: false,
        dataType: 'json',
        data: {
        },
        success: function (result) {  
            if(result){
                $('.total-mess').text(result.arrListMessage.length);
                var strNotify = '';
                $.each(result.arrListMessage, function(i,val){
                    var userInfo = jQuery.parseJSON(val.mess_content);
                    strNotify += '<a class="list-mess" rel="'+ val.mess_id +'" href="' + val.mess_url + '">\n\
                                    <span class="subject">\n\
                                        <span class="from">' + val.mess_title + '<br><span style="font-size:12px;">'+ userInfo.fullname + '<br>' + userInfo.phone + '</span></span>\n\
                                    </span>\n\
                                </a>';
                    if(i == 2){
                        return false;
                    }
                });
                $('.popup-notify li:first').append(strNotify);
            }
        }
    });
    $(document).on('click','.list-mess',function(){
        var messID = $(this).attr('rel');
        $.ajax({    
            type: "POST",
            url: baseurl + '/backend/message/edit',
            cache: false,
            dataType: 'json',
            data: {
                'messID': messID,
                'mess_status': 1
            },
            success: function (result) {
                if(result.st != 1){
                    bootbox.alert('<b>' + result.ms + '</b>');
                    return false;
                }
            }
        });
    })
});