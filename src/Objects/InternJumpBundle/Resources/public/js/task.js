$(document).ready(function() {

    $('.taskNote').hide();
    //intialize job users
//    if($('#form_internship').val() != ''){
//        $.ajax({
//            url: jobUsersUrl+"/"+$('#form_internship').val(),
//            dataType: 'json',
//            success: function(msg) {
//                //                var msg = $.parseJSON(msg);
//                //set users
//                $('#form_user').empty();
//                $.each(msg, function(k, v) {
//                    //display the key and value pair
//                    $('#form_user').append(
//                        $('<option></option>').val(k).html(v)
//                        );
//                });
//
//                //select task user for edit action
//                if(typeof userId != 'undefined')
//                    $('#form_user').val(userId);
//
//            },
//            complete: function(msg) {
//                $('#form_user').val(userId);
//                $("select.chzn-done").trigger("liszt:updated");
//            }
//        });
//    }
//
//
//    //change job users when jobs change
//    $('#form_internship').change(function(){
//        $('.loading').show();
//        $.ajax({
//            url: jobUsersUrl+"/"+$(this).val(),
//            dataType: 'json',
//            success: function(msg) {
//                //                var msg = $.parseJSON(msg);
//                //set users
//                $('#form_user').empty();
//                $.each(msg, function(k, v) {
//                    //display the key and value pair
//                    $('#form_user').append(
//                        $('<option></option>').val(k).html(v)
//                        );
//                });
//            },
//            complete: function(msg) {
//                $('.loading').hide();
//                $('#form_user').val(userId);
//                $("select.chzn-done").trigger("liszt:updated");
//            }
//        });
//
//    });


    if ($('#form_user').val() != '') {
        $('.loading').show();
        $.ajax({
            url: userJobsUrl + "/" + $('#form_user').val(),
            dataType: 'json',
            success: function(msg) {
                //                var msg = $.parseJSON(msg);
                //set users
                $('#form_internship').empty();
                $.each(msg, function(k, v) {
                    //display the key and value pair
                    $('#form_internship').append(
                            $('<option></option>').val(k).html(v)
                            );
                });

            },
            complete: function(msg) {
                $('.loading').hide();
//                $('#form_user').val(userId);
                $("select.chzn-done").trigger("liszt:updated");
            }
        });
    }


    //change job users when jobs change
    $('#form_user').change(function() {
        $('.loading').show();
        $.ajax({
            url: userJobsUrl + "/" + $(this).val(),
            dataType: 'json',
            success: function(msg) {
                //                var msg = $.parseJSON(msg);
                //set users
                $('#form_internship').empty();
                $.each(msg, function(k, v) {
                    //display the key and value pair
                    $('#form_internship').append(
                            $('<option></option>').val(k).html(v)
                            );
                });
            },
            complete: function(msg) {
                $('.loading').hide();
//                $('#form_user').val(userId);
                $("select.chzn-done").trigger("liszt:updated");
            }
        });

    });



});