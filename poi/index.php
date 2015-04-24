<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>PointsOfInterest</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />
</head>
<body>

<div class="container">
	<h1>Points of interest<a href="./req4.php"><button class="btn btn-primary pull-right">Requirement 4 &gt;</button></a></h1>
	<form>
		<label for="regions">Filter by region:</label>
		<select id="regions">
			<option value='?' selected>---- All ----</option>
		</select>
	</form>
	<div id="map1" style="width:100%; height:500px"> </div>
</div>

<div class="modal fade" id="add-POI-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Add a new Point of Interest @ (<span id="new_lat"></span>, <span id="new_lng"></span>)</h4>
      </div>
      <div class="modal-body">
        <form>
		  <div class="form-group">
		    <label for="name">Name</label>
		    <input type="text" class="form-control" id="name" placeholder="Name of the POI">
		  </div>
		  <div class="form-group">
		    <label for="type">Type</label>
		    <select id="type"></select>
		  </div>
		  <div class="form-group">
		    <label for="country">Country</label>
		    <input type="email" class="form-control" id="country" placeholder="Country">
		  </div>
		  <div class="form-group">
		    <label for="region">Region</label>
		    <input type="email" class="form-control" id="region" placeholder="Region">
		  </div>
		  <div class="form-group">
		    <label for="description">Description</label>
		    <textarea id="description" class="form-control"></textarea>
		  </div>
		</form>
      </div>
      <div class="modal-footer">
        	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        	<button type="button" id="save_poi_btn" class="btn btn-primary">Save POI</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.2.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
<script src="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js"></script>
<script type="text/javascript">

	var map;
	var regions_select = document.getElementById('regions');
	var markers = [];

	var new_lat = document.getElementById('new_lat');
	var new_lng = document.getElementById('new_lng');

	var types_select = document.getElementById('type');
	var save_poi_btn = document.getElementById('save_poi_btn');

	var last_add_lat;
	var last_add_lon;


	if ( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) &&
		navigator.geolocation) {
		 navigator.geolocation.getCurrentPosition (processPosition);

	}

    function processPosition(pos){
    	var new_pos = new L.LatLng(pos.coords.latitude, pos.coords.longitude);
    	map.setView(new_pos, 14);
	}

	/* Load the regions select */

	function load_regions(){
		// Loads the regions_select with all regions from the database
		var regions_request = new XMLHttpRequest();
	    regions_request.addEventListener ("load", receive_regions);
	    regions_request.open("GET" , "./api/poi/regions/");
	    regions_request.send();
	}


	function receive_regions(e){
		var regions = JSON.parse(e.target.responseText);

		for (var i = 0; i<regions.length; i++){
			regions_select.innerHTML += '<option>' + regions[i] + '</option>';
		}

	}

	/* Load the add POIs types */
	function load_types(){
		var regions_request = new XMLHttpRequest();
	    regions_request.addEventListener ("load", receive_types);
	    regions_request.open("GET" , "./api/poi/types/");
	    regions_request.send();
	}

	function receive_types(e){
		var types = JSON.parse(e.target.responseText);

		for (var i = 0; i<types.length; i++){
			types_select.innerHTML += (i==0?'<option selected>':'<option>') + types[i] + '</option>';
		}

	}

	var regions_select_handler = function(){
		//Handles when an user changes the region select
		if (regions_select.value=='?'){
			ajax_get_pois();
		} else {
			ajax_get_pois(regions_select.value);
		}
	}

	regions_select.onchange = regions_select_handler;

	function ajax_get_pois(region){
		//Function to make the API call
		var all_pois_request = new XMLHttpRequest();
	    all_pois_request.addEventListener ("load", receivePOIs);
	    var url = "./api/poi/?";
	    // Append the map bounds to the request
	    mapbounds = map.getBounds();
	    url += [ 
	    	'north_lat=' + mapbounds._northEast.lat,
	    	'east_lon=' + mapbounds._northEast.lng,
			'south_lat=' + mapbounds._southWest.lat,
			'west_lon=' + mapbounds._southWest.lng
	    	].join('&');

	    if (region!==undefined){
	    	url += "&region=" + region;
	    }
	    all_pois_request.open("GET" , url);
	    all_pois_request.send();

	}

	function receivePOIs(e){
		clear_markers();
		//Receives the POIs from the asynchronous petition
		var pois = JSON.parse(e.target.responseText);
		for (var i = 0; i<pois.length; i++){
			add_marker(pois[i]);
		}
	}

	function add_marker(poi){
		// Adds a marker for an spe
		var marker = new L.Marker(new L.LatLng(poi.lat, poi.lon));
		map.addLayer(marker);
		marker.bindPopup([ '<b>' + poi.name + '</b>',
							'<i>' + poi.type + '</i>',
							'"' + poi.description + '"'].join('<br>'));
		markers.push(marker);
	}

	function clear_markers(){
		// Removes all markers from the map
		for (var i=0; i<markers.length; i++){
			map.removeLayer(markers[i]);
		}
		markers = [];
		
	}


	function init(){

		load_regions();
		load_types();
		
	    
		map = new L.Map ("map1");
	    var attrib="Map data copyright OpenStreetMap contributors, Open Database Licence";

	    var layerOSM = new L.TileLayer
	        ("http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
	            { attribution: attrib } );       
	    map.addLayer(layerOSM);
	    var pos = new L.LatLng(50.9,-1.4);
	    map.setView(pos, 14);
	    map.on("click",click_handler);
	    map.on('moveend', map_movement_handler);

	    ajax_get_pois();

	}

	function click_handler(e){
		$('#add-POI-modal').modal('show')
		last_add_lat = e.latlng.lat;
		last_add_lon = e.latlng.lng;
		new_lat.innerHTML = e.latlng.lat.toFixed(5);
		new_lng.innerHTML = e.latlng.lng.toFixed(5);
	    //console.log("Click @ <" + e.latlng.lat + ", " + e.latlng.lng + ">");
	}

	function save_POI(){

		var new_name = document.getElementById('name').value;
		var new_type = types_select.value;
		var new_country = document.getElementById('country').value;
		var new_region = document.getElementById('region').value;
		var new_lon = last_add_lon;
		var new_lat = last_add_lat;
		var new_description = document.getElementById('description').value;

		if (new_name != '' &&
			new_type != '' &&
			new_country != '' &&
			new_region != '' &&
			new_lon != '' &&
			new_lat != '' &&
			new_description != ''){


			var add_POI_request = new XMLHttpRequest();
	    	add_POI_request.addEventListener ("load", add_POI_handler);
	    	var url = "./api/poi/";
	    	add_POI_request.open("POST" , url, true);
	    	var data = new FormData();
	    	data.append("name", new_name);
	    	data.append("type", new_type);
	    	data.append("country", new_country);
	    	data.append("region", new_region);
	    	data.append("lon", new_lon);
	    	data.append("lat", new_lat);
	    	data.append("description", new_description);
	    	add_POI_request.send(data);
	    	// Hide modal and clear data
	    	$('#add-POI-modal').modal('hide');

		} else {
			alert("All values are compulsory");
		}


	}

	var add_POI_handler = function(e){
		if (e.target.status==201){
			//fetch_reviews();
			var new_poi = JSON.parse(e.target.responseText);
			alert("The POI " + new_poi.name + " has been successfully added!");
			add_marker(new_poi);
			document.getElementById('name').value = '';
			document.getElementById('country').value = '';
			document.getElementById('region').value = '';
			last_add_lon = '';
			last_add_lat = '';
			document.getElementById('description').value = '';

		} else{
			alert("There was a problem adding your POI");
			$('#add-POI-modal').modal('show');
		}
		
	}

	function map_movement_handler(){
		if (regions_select.value=='?'){
			ajax_get_pois();
		} else {
			ajax_get_pois(regions_select.value);
		}
		
	}

window.onload = init;
save_poi_btn.onclick = save_POI;


</script>
</body>
</html>