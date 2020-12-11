// This example adds a search box to a map, using the Google Place Autocomplete
// feature. People can enter geographical searches. The search box will return a
// pick list containing a mix of places and predicted search terms.

// This example requires the Places library. Include the libraries=places
// parameter when you first load the API. For example:
// <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">

function handleLocationError(browserHasGeolocation, infoWindow, pos) {
    infoWindow.setPosition(pos);
    infoWindow.setContent('Saudi Arabia');
    // infoWindow.setContent(browserHasGeolocation ?
    //                       'Error: The Geolocation service failed.' :
    //                       'Error: Your browser doesn\'t support geolocation.');
}

function initAutocomplete() {
    var map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: 25.77, lng: 45.55}, //24.7136,  46.6753 riyadh
        zoom: 13,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    });
    var marker;
    var infoWindow = new google.maps.InfoWindow({map: map});
    $(".map-modal").on("shown.bs.modal", function(e) {
        google.maps.event.trigger(map, "resize");
        if (navigator.geolocation) {

            if (!($('#lat').val() == '' || $('#lat').val() == 0)){
                var pos = {
                    lat: parseFloat($('#lat').val()),
                    lng: parseFloat($('#long').val())
                };
                map.setCenter(pos);

                marker = new google.maps.Marker({
                    position: pos,
                    map: map,
                    title: 'Me'
                });
            }
            else {
                navigator.geolocation.getCurrentPosition(function (position) {

                    var pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    map.setCenter(pos);

                    marker = new google.maps.Marker({
                        position: pos,
                        map: map,
                        title: 'Me'
                    });

                }, function () {
                    handleLocationError(true, infoWindow, map.getCenter());
                });
            }
            
        } else {
            // Browser doesn't support Geolocation
            handleLocationError(false, infoWindow, map.getCenter());
        }

    });

    // Create the search box and link it to the UI element.
    var input = document.getElementById('pac-input');
    var searchBox = new google.maps.places.SearchBox(input);
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

    // Bias the SearchBox results towards current map's viewport.
    map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());
    });

    // Listen for the event fired when the user selects a prediction and retrieve
    // more details for that place.
    searchBox.addListener('places_changed', function() {
        var places = searchBox.getPlaces();

        if (places.length == 0) {
            return;
        }
        // For each place, get the icon, name and location.
        var bounds = new google.maps.LatLngBounds();
        places.forEach(function(place) {
            var icon = {
                url: place.icon,
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(17, 34),
                scaledSize: new google.maps.Size(25, 25)
            };

            if (place.geometry.viewport) {
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });
        map.fitBounds(bounds);

    });

    $('#addmap').click(function(){
        $('#closemap').click();
    });

    google.maps.event.addListener(map, "click", function (e) {
        var latLng = e.latLng;
        // Clear out the old markers.
        if(marker!= null){
            marker.setMap(null);
        }
        $.get("http://ipinfo.io", function(response) {
            //      console.log(response);
        }, "jsonp");
        var geocoder = new google.maps.Geocoder;
        //   var geo = new google.maps.TransitFare.DirectionsRoute;
        //     console.log(google.maps);
        geocoder.geocode({'location': latLng}, function(results, status) {
            if (status === 'OK') {
                if (results[1]) {
                    $('#lat').val(results[1].geometry.location.lat());
                    $('#long').val(results[1].geometry.location.lng());
                    $('#location').val(results[1].formatted_address);
                    //__________________________fetching city and state
                    result=results[0].address_components;
                    var info=[];
                    console.log("is Array");
                    console.log(results[0]);
                    results[0].address_components.forEach(function(item){

                        if(item.types[0]=="country"){
                            $('#country').val(item.long_name);
                            processForFlag(item.long_name);
                        }
                        if(item.types[0]=="locality"){
                            $('#city').val(item.long_name);
                        }
                    });

                    //_________________________done
                    marker = new google.maps.Marker({
                        position: latLng,
                        map: map,
                        title: results[1].formatted_address
                    });
                } else {
                    window.alert('No results found');
                }
            } else {
                window.alert('Geocoder failed due to: ' + status);
            }
        });
    });
}


