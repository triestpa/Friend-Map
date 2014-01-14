<!DOCTYPE html>
<html>
<head>
	<title>FriendMap</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<script src="js/jquery.js"></script>

	<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
	<link href="css/bootstrap-theme.min.css" rel="stylesheet" media="screen">
	<link href="css/my-styles.css" rel="stylesheet" media="screen">

	<script src="js/bootstrap.min.js"></script>

	<!-- Load the google map api -->
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD5jiD2pw8yK6dyh1brz_0jEBcjb7AzcUg&amp;sensor=false"></script>
	<script>

	var map;
	var center;

	function initialize() {

		  // Create an array of styles.
		  var styles = [
		  {
		  	stylers: [
		  	{ hue: "#007fff" },
		  	{ saturation: -25 }
		  	]
		  },{
		  	featureType: "road",
		  	elementType: "geometry",
		  	stylers: [
		  	{ lightness: 100 },
		  	{ visibility: "simplified" }
		  	]
		  },{
		  	featureType: "road",
		  	elementType: "labels",
		  	stylers: [
		  	{ visibility: "off" }
		  	]
		  }
		  ];

		// Create a new StyledMapType object, passing it the array of styles,
 		// as well as the name to be displayed on the map type control.
 		var styledMap = new google.maps.StyledMapType(styles,
 			{name: "Styled Map"});

 		var map_canvas = document.getElementById('map_canvas');
 		var map_options = {
 			center: new google.maps.LatLng(20, 0),
 			zoom: 2,
 			disableDefaultUI: true,
 			mapTypeId: google.maps.MapTypeId.ROADMAP
 		}
 		map = new google.maps.Map(map_canvas, map_options);
		    //Associate the styled map with the MapTypeId and set it to display.
		    map.mapTypes.set('map_style', styledMap);
		    map.setMapTypeId('map_style');
		}


		google.maps.event.addDomListener(window, 'load', function(){

			initialize();

			//The map will re-center when the page size changes
			google.maps.event.addDomListener(map, 'idle', function(){
				center = map.getCenter();
			});

		});

		$(window).resize(function(){
			map.setCenter(center);
		});




		function login() {
			FB.login(function(response) {
				if (response.authResponse) {
					console.log('Welcome!  Fetching your information.... ');
					FB.api('/me', function(response) {
						console.log('Good to see you, ' + response.name + '.');
						console.log('Your location is ' + response.location.name  + '.');
					});
				} else {
					console.log('User cancelled login or did not fully authorize.');
				}
			}, {scope: 'user_location, friends_location'});
		}


		function loadFriendList() {
			var friendList_HTML = "";

			FB.api('me/friends', function(response) {
				$.each(response.data,function(index,friend) {
					FB.api(friend.id, function(response) {
						friendList_HTML += "<a href=\"#\" class=\"list-group-item\">" + friend.name + '  lives in  ' + response.location.name + '.' + "</a>";
						console.log(friend.name + '  lives in  ' + response.location.name + '.');
						$("#friendList").html(friendList_HTML);
					});
				});
			});
		}


		function getMyLocationPoints() {
			var myLatlng;
			var locationTitle;
			FB.api('/me', function(response) {
				console.log('Hello, ' + response.name + '.'); 
				var location = response.location.name;       			
				console.log('Your location is ' +  location + '.');

				//Query geonames for location coordinates
				var queryURL = "http://api.geonames.org/searchJSON?q=" + location + "&maxRows=10&username=triestpa";

				$.getJSON(queryURL)
				.done(function( data ){
					console.log(data);
					var firstmatch = data.geonames[0];
					console.log("Name: " + firstmatch.name);
					console.log("Lat: " + firstmatch.lat);
					console.log("Lng: " + firstmatch.lng);
					myLatlng = new google.maps.LatLng(firstmatch.lat, firstmatch.lng);
					locationTitle = firstmatch.name;

					//Add Location to map
					var marker = new google.maps.Marker({
						position: myLatlng,
						map: map,
						title: locationTitle
					});
				})
				.fail(function( jqxhr, textStatus, error ) {
					var err = textStatus + ", " + error;
					console.log( "Request Failed: " + err );
				});
			});
		}


		function getFriendsLocationsPoints() {
			var friendLatlng;
			var locationTitle;
			FB.api('me/friends', function(response) {
				$.each(response.data,function(index,friend) {
					FB.api(friend.id, function(response) {
						var location = response.location.name;       			
						var queryURL = "http://api.geonames.org/searchJSON?q=" + location + "&maxRows=10&username=triestpa";
						$.getJSON(queryURL)
						.done(function( data ){
							console.log(data);
							var firstmatch = data.geonames[0];
							console.log("Name: " + firstmatch.name);
							console.log("Lat: " + firstmatch.lat);
							console.log("Lng: " + firstmatch.lng);
							friendLatlng = new google.maps.LatLng(firstmatch.lat, firstmatch.lng);
							locationTitle = firstmatch.name;

							//Add Location to map
							var marker = new google.maps.Marker({
								position: friendLatlng,
								map: map,
								title: locationTitle
							});

						})
						.fail(function( jqxhr, textStatus, error ) {
							var err = textStatus + ", " + error;
							console.log( "Request Failed: " + err );
						});
					});
				});
			});
		}


</script>

</head>
<body>

	<!-- Setup Facebook Integraton -->
	<div id="fb-root"></div>

	<script>
	window.fbAsyncInit = function() {
		    // init the FB JS SDK
		    FB.init({
		      appId      : '681219568576604',                    // App ID from the app dashboard
		      status     : true,                                 // Check Facebook Login status
		      xfbml      : true                                  // Look for social plugins on the page
		  });

	    	// Additional initialization code such as adding Event Listeners goes here

	    };

		  // Load the SDK asynchronously
		  (function(){
		     // If we've already installed the SDK, we're done
		     if (document.getElementById('facebook-jssdk')) {return;}

		     // Get the first script element, which we'll use to find the parent node
		     var firstScriptElement = document.getElementsByTagName('script')[0];

		     // Create a new script element and set its id
		     var facebookJS = document.createElement('script'); 
		     facebookJS.id = 'facebook-jssdk';

		     // Set the new script's source to the source of the Facebook JS SDK
		     facebookJS.src = "//connect.facebook.net/en_US/all.js";

		     // Insert the Facebook JS SDK into the DOM
		     firstScriptElement.parentNode.insertBefore(facebookJS, firstScriptElement);
		 }());

		  </script>

		  <!-- Navbar -->
		  <div class="navbar navbar-default navbar-fixed-top">
		  	<div class="container">

		  		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
		  			<span class="icon-bar"></span>
		  			<span class="icon-bar"></span>
		  			<span class="icon-bar"></span>
		  		</button>

		  		<a class="navbar-brand text-muted" href="#">CouchIt</a>
		  		<div class="collapse navbar-collapse">
		  			<ul class="nav navbar-nav navbar-right">
		  				<li class="active"><a href="#">Map</a></li>
		  				<li><a href="#">Overview</a></li>
		  				<li><a href="#">About Us</a></li>
		  				<li><a href="#">Future Plans</a></li>
		  			</ul>

		  		</div>
		  	</div>
		  </div>
		  <!-- End navbar -->


		  <div class="jumbotron">  
		  	<div class="container">  
		  		<h1>The CouchIt Map</h1>
		  		<p class="lead">Find a friend to crash with!</p>
		  	</div>
		  </div>

		  <div class="row">
		  	<div class="col-sm-9">
		  		<div id="map_canvas" class=""></div>
		  	</div>

		  	<div class="col-sm-3" id="friend-list">

		  		<button id="Login" class="btn btn-default testButtons"> Log In </button> 
		  		<button id="Logout" class="btn btn-default testButtons"> Log Out </button> 
		  		<button id="LoadList" class="btn btn-default testButtons"> Load List</button> 
		  		<button id="LoadMyLocation" class="btn btn-default testButtons"> Load My Location</button> 
		  		<button id="LoadFriendsLocations" class="btn btn-default testButtons"> Load Friends Locations</button> 



		  		<script>
	  				//-Set Log In button:
	  				document.getElementById("Login").onclick = login;

	  				document.getElementById("Logout").onclick = function(){
	  					FB.logout(function(response) {
	  						console.log("User is logged out");
	  					});
	  				};

	  				document.getElementById("LoadList").onclick = loadFriendList;

	  				document.getElementById("LoadMyLocation").onclick = getMyLocationPoints;
	  				document.getElementById("LoadFriendsLocations").onclick = getFriendsLocationsPoints;



	  				</script>

	  				<div class="list-group" id ="friendList"></div>

	  			</div>
	  		</div>
	  	</body>
	  	</html>