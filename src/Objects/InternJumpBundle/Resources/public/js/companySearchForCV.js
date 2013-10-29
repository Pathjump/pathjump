$(document).ready(function() {
    //check if we have any country selected
    if ($('#form_country').val()) {
        //get the correct city and state select from the server
        changeCityAndStateSelects($('#form_country').val());
    }
    //on the change of country select change the city and state selects values
    $('#form_country').on('change', function() {
        if ($(this).val()) {
            //change the city and state selects
            changeCityAndStateSelects($(this).val());
        } else {
            //empty the city select
            $('#form_city').empty().trigger('liszt:updated');
            //empty the state select
            $('#form_state').empty().trigger('liszt:updated');
            refreshPage(1);
        }
    });
});

function changeCityAndStateSelects(countryId) {
    //display the loader
    $('.loading').show();
    $.ajax({
        url: countrySelectsUrl + countryId,
        success: function(msg) {
            msg = $.parseJSON(msg);
            //get the current selected country if exist
            var selectedCity = $('#form_city').val();
            //empty the city select
            $('#form_city').empty();
            //append the new data
            $('#form_city').append('<option value=""></option>');
            $.each(msg.cities, function(k, v) {
                if (selectedCity == v) {
                    $('#form_city').append(
                            $('<option selected="selected"></option>').val(v).html(v)
                            );
                } else {
                    $('#form_city').append(
                            $('<option></option>').val(v).html(v)
                            );
                }
            });
            $('#form_city').trigger('liszt:updated');
            //get the current selected country if exist
            var selectedState = $('#form_state').val();
            //empty the state select
            $('#form_state').empty();
            //append the new data
            $('#form_state').append('<option value=""></option>');
            $.each(msg.states, function(k, v) {
                if (selectedState == v) {
                    $('#form_state').append(
                            $('<option selected="selected"></option>').val(v).html(v)
                            );
                } else {
                    $('#form_state').append(
                            $('<option></option>').val(v).html(v)
                            );
                }
            });
            $('#form_state').trigger('liszt:updated');
        },
        complete: function() {
            //hide the loader
            $('.loading').hide();
            refreshPage(1);
        }
    });
}

function refreshPage(page) {
    //display the loader
    $('.loading').show();
    $.ajax({
        url: $('#cv-search-form').attr('action'),
        data: $('#cv-search-form').serialize() + '&page=' + page,
        success: function(data) {
            $('#tab1').html(data);
        },
        complete: function() {
            //hide the loader
            $('.loading').hide();
        }
    });
}