var Profile = {
    index: function () {
        $(document).ready(function () {
            $('#info').parent().addClass('active');
            $('.btn-submit').hide();
            $('#get-alert').hide();

            $('.menu').on('click', '#info', function () {
                $('.profile-info').load(baseurl + '/backend/profile/info', function () {
                    $('#info').parent().addClass('active');
                    $('#edit').parent().removeClass('active');
                });
            });
            $('.menu').on('click', '#edit', function () {
                $('.profile-info').load(baseurl + '/backend/profile/edit', function () {
                    $('#edit').parent().addClass('active');
                    $('#info').parent().removeClass('active');
                });
            });

            Profile.showImage();
            Profile.edit();
        });
    },
    edit: function () {
        $('.profile-info').on('click', '.btn-edit', function () {
            var name = $('.name').val(),
                    old_password = $('.old-password').val(),
                    new_password = $('.new-password').val(),
                    re_new_password = $('.re-new-password').val(),
                    email = $('.email').val(),
                    gender = $('.gender').find(':selected').val(),
                    telephone = $('.telephone').val(),
                    birthdate = $('.birthdate').val(),
                    address = $('.address').val(),
                    city = $('#city').find(':selected').val(),
                    district = $('#district').find(':selected').val();
            $.ajax({
                type: "POST",
                url: baseurl + '/backend/profile/edit',
                cache: false,
                dataType: 'json',
                data: {
                    name: name, old_password: old_password, new_password: new_password, re_new_password: re_new_password, email: email, gender: gender,
                    telephone: telephone, birthdate: birthdate, address: address, city: city, district: district
                },
                success: function (result) {
                    var html = '';
                    if (result.success === 1) {
                        html += '<div class="alert alert-success alert-block fade in">';
                        html += '<button class="close close-sm" type="button" data-dismiss="alert"><i class="fa fa-times">x</i></button>';
                        html += '<h4><i class="fa fa-ok-sign"></i> Hoàn tất!</h4>';
                        html += '<p>- ' + result.message + '</p>';
                        html += '</div>';
                        $('.get-fullname').text(result.data.user_fullname);
                        $('.get-email').text(result.data.user_email);

                        var arrFullname = result.data.user_fullname.split(" ");
                        arrFullname = arrFullname.slice(arrFullname.length - 1, arrFullname.length);

                        $('span.username').text('Xin chào ' + arrFullname);
                    } else if (result.error === 1) {
                        html += '<div class="alert alert-danger alert-block fade in">';
                        html += '<button class="close close-sm" type="button" data-dismiss="alert"><i class="fa fa-times">x</i></button>';
                        html += '<h4><i class="fa fa-times-sign"></i> Lỗi!</h4>';
                        for (var i = 0; i < result.data.length; i++) {
                            html += '<p>- ' + result.data[i] + '</p>';
                        }
                        html += '</div>';
                    }
                    $('#get-alert').html(html);
                    $('#get-alert').show();
                }
            });
        });

        $('.profile-info').on('change', '#city', function () {
            var city = $(this).find(':selected').val();
            $.ajax({
                type: "POST",
                url: baseurl + '/backend/profile/changecity',
                cache: false,
                dataType: 'json',
                data: {
                    city: city,
                },
                success: function (result) {
                    if (result.success === 1) {
                        var html = '';
                        for (var i = 0; i < result.data.length; i++) {
                            var item = result.data[i];
                            html += '<option value="' + item.dist_id + '">' + item.dist_name + '</option>';
                        }
                        $('#district').html(html);
                        $('.get-district').fadeIn();
                    } else if (result.error === 1) {
                        $('.get-district').fadeOut();
                    }
                }
            });
        });
    },
    showImage: function () {
        $(document).ready(function () {
            function readImage(file) {

                var reader = new FileReader();
                var image = new Image();

                reader.readAsDataURL(file);
                reader.onload = function (_file) {
                    image.src = _file.target.result;              // url.createObjectURL(file);
                    image.onload = function () {
                        w = this.width,
                                h = this.height,
                                t = file.type, // ext only: // file.type.split('/')[1],
                                n = file.name,
                                s = ~~(file.size / 1024) + 'KB';
                        $('#uploadPreview').html('<img src="' + this.src + '">');
                        $('.btn-submit').show();
                        $('.btn-submit').attr('style', '');
                    };
                    image.onerror = function () {
                        bootbox.alert('<b> Hình ảnh không hợp lệ</b>');
                    };
                };

            }
            $("#categoryImages").change(function (e) {
                if (this.disabled)
                    return bootbox.alert('File upload not supported!');
                var F = this.files;
                if (F && F[0])
                    for (var i = 0; i < F.length; i++)
                        readImage(F[i]);

                var data = new FormData();
                jQuery.each(jQuery('#categoryImages')[0].files, function (i, file) {
                    data.append('file-' + i, file);
                });
                if (data != "") {
                    $.ajax({
                        url: baseurl + '/backend/profile/index', // Url to which the request is send
                        type: 'POST', // Type of request to be send, called as method
                        data: data, // Data sent to server, a set of key/value pairs (i.e. form fields and values)
                        contentType: false, // The content type used when sending data to the server.
                        cache: false, // To unable request pages to be cached
                        processData: false, // To send DOMDocument or non processed data file it is set to false
                        success: function (data)   // A function to be called if request succeeds
                        {
                            if (data) {
                                $('.get-alert-upload').html('<h5 class="color-success">Thay đổi ảnh thành công</h5>').hide().fadeIn('slow', function () {
                                    $('.get-alert-upload').fadeOut(1000);
                                });
                            }
                        }
                    });
                } else {
                    alert(data);
                }
            });

            $("#camera").change(function (e) {
                if (this.disabled)
                    return bootbox.alert('File upload not supported!');
                var F = this.files;
                if (F && F[0])
                    for (var i = 0; i < F.length; i++)
                        readImage(F[i]);

                var data = new FormData();
                jQuery.each(jQuery('#camera')[0].files, function (i, file) {
                    data.append('file-' + i, file);
                });
                $.ajax({
                    url: baseurl + '/backend/profile/index', // Url to which the request is send
                    type: "POST", // Type of request to be send, called as method
                    data: data, // Data sent to server, a set of key/value pairs (i.e. form fields and values)
                    contentType: false, // The content type used when sending data to the server.
                    cache: false, // To unable request pages to be cached
                    processData: false, // To send DOMDocument or non processed data file it is set to false
                    success: function (data)   // A function to be called if request succeeds
                    {
                        $('.get-alert-upload').html('<h5 class="color-success">Thay đổi ảnh thành công</h5>').hide().fadeIn('slow', function () {
                            $('.get-alert-upload').fadeOut(1000);
                        });
                    }
                });
            });
        });

    },
};
