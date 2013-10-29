$(document).ready(function() {
    //intialize country cities and states
    if ($('#form_country').val() != '') {
        $.ajax({
            url: countryCitiesStatesUrl + "/" + $('#form_country').val(),
            success: function(msg) {
                var msg = $.parseJSON(msg);
                //set country cities
//                $('#form_city').empty().append(
//                    $('<option></option>').val('').html('--- choose city ---')
//                    );
//                $.each(msg.cities, function(k, v) {
//                    //display the key and value pair
//                    $('#form_city').append(
//                        $('<option></option>').val(v).html(v)
//                        );
//                });
                //select job city for edit action
                if (typeof jobCity != 'undefined')
                    $('#form_city').val(jobCity);
                //set country states
                $('#form_state').empty().append(
                        $('<option></option>').val('').html('--- choose state ---')
                        );
                $.each(msg.states, function(k, v) {
                    //display the key and value pair
                    $('#form_state').append(
                            $('<option></option>').val(v).html(v)
                            );
                });
                //select job state for edit action
                if (typeof jobState != 'undefined')
                    $('#form_state').val(jobState);
            },
            complete: function(msg) {
                $("select.chzn-select").trigger("liszt:updated");
            }
        });
    }


    //change cities and states on country chacnge
    $('#form_country').change(function() {
        $('#loading').show();
        $.ajax({
            url: countryCitiesStatesUrl + "/" + $(this).val(),
            success: function(msg) {
                var msg = $.parseJSON(msg);
                //set country cities
//                $('#form_city').empty().append(
//                    $('<option></option>').val('').html('--- choose city ---')
//                    );
//                $.each(msg.cities, function(k, v) {
//                    //display the key and value pair
//                    $('#form_city').append(
//                        $('<option></option>').val(v).html(v)
//                        );
//                });
                //set country states
                $('#form_state').empty().append(
                        $('<option></option>').val('').html('--- choose state ---')
                        );
                $.each(msg.states, function(k, v) {
                    //display the key and value pair
                    $('#form_state').append(
                            $('<option></option>').val(v).html(v)
                            );
                });
//                $("#form_state option").filter(function() {
//                    //may want to use $.trim in here
//                    return $(this).text() == defaultStateName;
//                }).attr('selected', true);
            },
            complete: function(msg) {
                $('#loading').hide();
                $("select.chzn-select").trigger("liszt:updated");
            }
        });
    });

    //confirm job delete
    $('#deleteJob').click(function() {
        jConfirm('ok', 'cancle', 'Are you sure you want to delete this job', 'Delete Job', function(r) {
            if (r == true) {
                window.location = $(this).attr('deleteUrl');
            } else {
                return false;
            }
        });
    });

    //refresh job position by zipcode
    $('#getMyPosition').click(function() {
        //get zipcode
        zipcode = $('#form_zipcode').val();
        if ($.trim(zipcode)) {
            //hide link
            $('#getMyPosition').hide();
            //show loading img
            $('#getMyPositionImg').show();
            $.ajax({
                url: getPositionUrl + "/" + zipcode,
                success: function(msg) {
                    if (msg != 'faild') {
                        msg = msg.split('|');
                        latitude = msg['0'];
                        longitude = msg['1'];
                        //set lat and lng to input fields
                        $('#form_Latitude').val(msg['0']);
                        $('#form_Longitude').val(msg['1']);
                    }
                },
                complete: function(msg) {
                    //show link
                    $('#getMyPosition').show();
                    //hide loading img
                    $('#getMyPositionImg').hide();

                    initialize();
                }
            });
        } else {
            jAlert(no_zipcode_message_new_job_page)
        }
    });

    //transition
    $('.jobNextStep').click(function() {
        var location = $(this).attr('data-href');
        var currentLocation = $(this).attr('data-current-location');

        $("#" + currentLocation).transition({opacity: 0.1, scale: 0.3}, 700, function() {
            $('#' + currentLocation).hide().transition({opacity: 1, scale: 1});

            $("#" + location).fadeIn(500, function() {
                if (location == '3rdstep') {
                    initialize();
                }
            });
        });
    });
    $('a.jobPrevStep').click(function() {
        var location = $(this).attr('href');
        var currentLocation = $(this).attr('data-current-location');

        $("#" + currentLocation).transition({opacity: 0.1, scale: 0.3}, 700, function() {
            $('#' + currentLocation).hide().transition({opacity: 1, scale: 1});

            $("#" + location).fadeIn(500);
        });
        return false;
    });

    //check if form error
    if ($('.controls .alert.alert-error').length > 0) {
        var firstErrorLocation = $('.controls .alert.alert-error:first');
        //hide all steps
        $('#1ststep,#2ndstep,#3rdstep').hide(0, function() {
            if ($('#1ststep').find(firstErrorLocation).length > 0) {
                $("#1ststep").fadeIn(500);
            } else if ($('#2ndstep').find(firstErrorLocation).length > 0) {
                $("#2ndstep").fadeIn(500);
            } else {
                $("#3rdstep").fadeIn(500);
                initialize();
            }
        });
    }
});