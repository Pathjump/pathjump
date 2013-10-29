$(document).ready(function(){
    //Function for Choosen >> page of inbox
    $(".chzn-select").chosen();
    $(".chzn-select-deselect").chosen({
        allow_single_deselect:true
    });
    $('#pageination').hide();


    //intialize country cities and states
    if($('#form_country').val() != ''){
        $.ajax({
            url: countryCitiesStatesUrl+"/"+$('#form_country').val(),
            success: function(msg) {
                var msg = $.parseJSON(msg);
                //set country cities
                $('#form_city').empty().append(
                    $('<option></option>').val('').html('--- choose city ---')
                    );
                $.each(msg.cities, function(k, v) {
                    //display the key and value pair
                    $('#form_city').append(
                        $('<option></option>').val(v).html(v)
                        );
                });
                //select job city for edit action
                if(typeof jobCity != 'undefined')
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
                if(typeof jobState != 'undefined')
                    $('#form_state').val(jobState);

            },
            complete: function(msg) {


                if(stateParam != ''){
                    $('#form_state').val(stateParam);
                }
                $("select.chzn-select").trigger("liszt:updated");
                $('#search_button').click();

            }
        });
    }


    //change cities and states on country chacnge
    $('#form_country').change(function(){
        //if empty ---choose country--- is chosen refresh and empty city and state);
        if($(this).val()== ""){
            $('#form_city').empty().append($('<option></option>').val('').html('--- choose city ---'));
            $('#form_state').empty().append($('<option></option>').val('').html('--- choose state ---'));
            $("select.chzn-select").trigger("liszt:updated");
            return false;
        }


        $.ajax({
            url: countryCitiesStatesUrl+"/"+$(this).val(),
            success: function(msg) {
                var msg = $.parseJSON(msg);
                //set country cities
                $('#form_city').empty().append(
                    $('<option></option>').val('').html('--- choose city ---')
                    );
                $.each(msg.cities, function(k, v) {
                    //display the key and value pair
                    $('#form_city').append(
                        $('<option></option>').val(v).html(v)
                        );
                });
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
            },
            complete: function(msg) {
                $("select.chzn-select").trigger("liszt:updated");
            }
        });
    });



});
