function initialize() {
    var latlng = new google.maps.LatLng(latitude,longitude); 
    
    var myOptions = {
        zoom: 5,
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("map_canvas"),
        myOptions);
                    
    
    var marker = new google.maps.Marker({
        position: latlng,
        map: map,
        animation: google.maps.Animation.DROP,
        title: 'the job location'
        });
    

}
