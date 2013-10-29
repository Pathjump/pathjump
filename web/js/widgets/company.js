$(document).ready(function() {
    //get request data
    if ($('#ij-company-widget').length > 0) {
        var width = $('#ij-company-widget').attr('data-width');
        if ($.trim(width) == "" || parseInt($.trim(width)) < 250)
            width = 250;

        var height = $('#ij-company-widget').attr('data-height');
        if ($.trim(height) == "" || parseInt($.trim(height)) < 250)
            height = 250;

        var border = $('#ij-company-widget').attr('data-border');

        var href = $('#ij-company-widget').attr('data-href');

        //append the iframe
        $('#ij-company-widget').html("<iframe frameborder='"+border+"' width='" + width + "' height='" + height + "' src='" + href + "'></iframe>");
    } else {
        alert('Please add the div to your html.')
    }
});