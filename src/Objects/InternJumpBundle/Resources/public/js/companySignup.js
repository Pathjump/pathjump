$(document).ready(function(){
    
    $('.establishmentDate').datepicker({
        changeYear: true, 
        dateFormat: 'yy-mm-dd', 
        maxDate: new Date(), 
        minDate: new Date('1800')
    });
    
    //get the correct city select from the server
    changeCityAndStateSelects($('#form_country').val());

    //on the change of country select change the city and state selects values
    $('#form_country').on('change', function() {
        //change the city and state selects
        changeCityAndStateSelects($(this).val());
    });
    
    //width for Rectangle h1 Bar
    empTitleBar = $('#empSignup .wrapper').width();
    $('#empSignup > .wrapper h1 div').width(empTitleBar-216);
    //Function for Choosen >> page of inbox
    $(".chzn-select").chosen();
    $("#form_establishmentDate_month").chosen();
    $("#form_establishmentDate_day").chosen();
    $("#form_establishmentDate_year").chosen();
    $(".chzn-select-deselect").chosen({
        allow_single_deselect:true
    });
    
    //refresh job position by zipcode
    $('#getMyPosition').click(function(){
        //get zipcode 
        zipcode = $('#form_zipcode').val();
        if($.trim(zipcode)){
            //hide link
            $('#getMyPosition').hide();
            //show loading img
            $('#getMyPositionImg').show();
            $.ajax({
                url: getPositionUrl+"/"+zipcode,
                success: function(msg) {
                    if(msg != 'faild'){
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
        }else{
            jAlert(no_zipcode_message_new_job_page)
        }
    });
    
    initialize();
});

/**
 * this function will read file input src 
 */
function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#yourImage').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}


function changeCityAndStateSelects(countryId) {
    //display the loader
    $('.loading').show();
    $.ajax({
        url: countrySelectsUrl + countryId,
        success: function(msg) {
            msg = $.parseJSON(msg);
            //empty the city select
            //            $('#form_city').empty();
            //append the new data
            //            $.each(msg.cities, function(k, v) {
            //                $('#form_city').append(
            //                    $('<option selected="selected"></option>').val(v).html(v)
            //                    );
            //            });
            //empty the state select
            $('#form_state').empty();
            //append the new data
            $.each(msg.states, function(k, v) {
                $('#form_state').append(
                    $('<option selected="selected"></option>').val(v).html(v)
                    );
            });
            if(typeof defaultStateName != 'undefined'){
                $("#form_state option").filter(function() {
                    //may want to use $.trim in here
                    return $(this).text() == defaultStateName; 
                }).attr('selected', true);
            }else if(typeof companyState != 'undefined'){
                $("#form_state option").filter(function() {
                    //may want to use $.trim in here
                    return $(this).text() == companyState; 
                }).attr('selected', true);
            }
        },
        complete: function() {
            //hide the loader
            $('.loading').hide();
            $("select.chzn-select").trigger("liszt:updated");
        }
    });
}

function initialize() {
    var latlng = new google.maps.LatLng(latitude,longitude); 
    
    var myOptions = {
        zoom: 5,
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("map_canvas"),
        myOptions);
                    
    var infowindow = new google.maps.InfoWindow();
    var marker = new google.maps.Marker({
        position: latlng,
        map: map,
        animation: google.maps.Animation.DROP,
        title: 'the job location'
    });
    infowindow.setContent(map_change_location_message);
    infowindow.open(map, marker);

    google.maps.event.addListener(map, 'click', function(event) {
        infowindow.close();
        
        var currentLat = event.latLng.lat();
        var currentLng = event.latLng.lng();
                
        var position = marker.getPosition();
        position = position.toString();
        
        position = position.split(',');
        position['0'] = position['0'].replace("(", "");
        position['1'] = position['1'].replace(")", "");
                    
        var newPosition = "<strong>Lat : </strong>"+currentLat+"<br><strong>Lng : </strong>"+currentLng;
        
        infowindow.setContent(newPosition);
        infowindow.open(map, marker);
        
        marker.setPosition(event.latLng);
        //set lat and lng to input fields
        $('#form_Latitude').val(currentLat);
        $('#form_Longitude').val(currentLng);
    });
}
