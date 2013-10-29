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
