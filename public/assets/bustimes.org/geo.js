function gotoUrl(location){
	window.location = 'location/' + location[0] + '/' + location[1];
}
function geoCode(){
	var geocodeRequest = new google.maps.Geocoder;
	var request = {
		address: document.getElementById('searchBox').value,
		region: 'uk'
	};
	geocodeRequest.geocode(request, function(results, status) {
	  if (status == google.maps.GeocoderStatus.OK) {
        gotoUrl([results[0].geometry.location.lat(), results[0].geometry.location.lng()]);
      } else if (status == google.maps.GeocoderStatus.ZERO_RESULTS){
      	alert("We couldn't work out where the location you entered is. Try clarifying it (adding a city, for example) and giving it another go.")
      } else {
        alert("Finding the location you entered didn't work due to an unknown error on our end. Try giving it another go.");
      }

	});
}
function geoLocate(){
	var geolocateIcon = document.getElementById('geolocateIcon');
	var geolocateButton = document.getElementById('geolocateButton');
	function useLocation(location){
		geolocateButton.innerHTML = "Found you!"
		geolocateIcon.className = "glyphicon glyphicon-ok-sign";
		gotoUrl([location.coords.latitude, location.coords.longitude]);
	}
	function useError(error){
		if (error.code == 1) {
		    geolocateButton.innerHTML = "Location access blocked";
		    geolocateIcon.className = "glyphicon glyphicon-lock";
  		} else if (error.code == 2 || error.code == 3) {
		    geolocateButton.innerHTML = "Location unavailable";
		    geolocateIcon.className = "glyphicon glyphicon-question-sign";
		}
	}
	if ("geolocation" in navigator){
		geolocateButton.innerHTML = 'Finding you...'
		geolocateIcon.className = "glyphicon glyphicon-time";
		navigator.geolocation.getCurrentPosition(useLocation, useError, {timeout: 20000});
	} else {
		geolocateButton.innerHTML = "Geolocation not supported"
		geolocateIcon.className = "glyphicon glyphicon-remove-sign";
		alert("Your browser does not support geolocation, please enter your location instead.");
	}
}