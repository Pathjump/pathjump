var autoCompleteUrl = cityAutoCompleteUrl;
$(document).ready(function() {
    autoCompleteUrl += "?country=" + $('#form_country').val();
    $('.autocomplete').autocomplete({
        autoFocus: true,
        minLength: 3,
        source: autoCompleteUrl
    });
    
    //on the change of country select change the city and state selects values
    $('#form_country').on('change', function() {
        //destroy the auto complete
        $('.autocomplete').autocomplete("destroy");
//        $('.autocomplete').val('');
        //change the source url
        autoCompleteUrl = cityAutoCompleteUrl + "?country=" + $(this).val();
        //rebuild the autocomplete
        $('.autocomplete').autocomplete({
            autoFocus: true,
            minLength: 3,
            source: autoCompleteUrl
        });
    });
});

