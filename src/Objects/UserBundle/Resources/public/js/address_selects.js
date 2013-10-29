var autoCompleteUrl = cityAutoCompleteUrl;
$(document).ready(function() {
    autoCompleteUrl += "?country=" + $('#form_country').val();
    $('.autocomplete').autocomplete({
        autoFocus: true,
        minLength: 3,
        source: autoCompleteUrl
    });
    //get the correct city and state select from the server
    changeStateSelect($('#form_country').val());
    //on the change of country select change the city and state selects values
    $('#form_country').on('change', function() {
        //destroy the auto complete
        $('.autocomplete').autocomplete("destroy");
        //change the source url
        autoCompleteUrl = cityAutoCompleteUrl + "?country=" + $(this).val();
        //rebuild the autocomplete
        $('.autocomplete').autocomplete({
            autoFocus: true,
            minLength: 3,
            source: autoCompleteUrl
        });
        //change the city and state selects
        changeStateSelect($(this).val());
    });
});

function changeStateSelect(countryId) {
    //display the loader
    $('#loading').show();
    $.ajax({
        url: countrySelectsUrl + countryId,
        success: function(msg) {
            msg = $.parseJSON(msg);
            //get the current selected country if exist
            var selectedState = $('#form_state').val();
            //empty the state select
            $('#form_state').empty();
            //append the new data
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

            if(typeof userState != 'undefined')
                $('#form_state').val(userState);
        },
        complete: function() {
            //hide the loader
            $('#loading').hide();
            $('.select-box select.state').selectbox('detach');
            $('.select-box select.state').selectbox('attach');
        }
    });
}