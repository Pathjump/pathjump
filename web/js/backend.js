$(document).ready(function() {
    //check if we have a country select in this page
    if($('select.countrySelect').length > 0) {
        //display the loader
        $('#objectsLoader').show();
        //load the countries data from the server
        $.ajax({
            url: countriesUrl,
            success: function(msg) {
                msg = $.parseJSON(msg);
                //get the current selected country if exist
                var selectedCountry = $('select.countrySelect').val();
                //clear the select
                $('select.countrySelect').empty();
                //append the new data
                $.each(msg, function(k, v) {
                    if(selectedCountry && selectedCountry == k){
                        $('select.countrySelect').append(
                            $('<option selected="selected"></option>').val(k).html(v)
                            );
                    } else {
                        $('select.countrySelect').append(
                            $('<option></option>').val(k).html(v)
                            );
                    }
                });
                //get the correct city select from the server
                changeCityAndStateSelects($('select.countrySelect').val());
            }
        });
    }
    //bind the chosen effect on any select with class chosen even after coming with ajax
    $("select.chosen").livequery(function() {
        $(this).chosen();
    });
    //on the change of country select change the city and state selects values
    $('select.countrySelect').on('change', function() {
        //change the city and state selects
        changeCityAndStateSelects($(this).val());
    });
});

/**
 * this fucntion is used to fill the cities select and the states select with values for a certain country id
 * @author Mahmoud
 */
function changeCityAndStateSelects(countryId) {
    //display the loader
    $('#objectsLoader').show();
    $.ajax({
        url: countrySelectsUrl + countryId,
        success: function(msg) {
            msg = $.parseJSON(msg);
            //get the current selected country if exist
            var selectedCity = $('select.citySelect').val();
            //empty the city select
            $('select.citySelect').empty();
            //append the new data
            $.each(msg.cities, function(k, v) {
                if(selectedCity == v){
                    $('select.citySelect').append(
                        $('<option selected="selected"></option>').val(v).html(v)
                        );
                } else {
                    $('select.citySelect').append(
                        $('<option></option>').val(v).html(v)
                        );
                }
            });
            //get the current selected country if exist
            var selectedState = $('select.stateSelect').val();
            //empty the state select
            $('select.stateSelect').empty();
            //append the new data
            $.each(msg.states, function(k, v) {
                if(selectedState == v){
                    $('select.stateSelect').append(
                        $('<option selected="selected"></option>').val(v).html(v)
                        );
                } else {
                    $('select.stateSelect').append(
                        $('<option></option>').val(v).html(v)
                        );
                }
            });
            //update chosen
            $(".chosen").trigger("liszt:updated");
        },
        complete: function() {
            //hide the loader
            $('#objectsLoader').hide();
        }
    });
}
