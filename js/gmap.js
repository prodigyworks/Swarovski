var map;

var localSearch = new GlocalSearch();

var icon = new GIcon();
icon.image = "http://www.google.com/mapfiles/marker.png";
icon.shadow = "http://www.google.com/mapfiles/shadow50.png";
icon.iconSize = new GSize(20, 34);
icon.shadowSize = new GSize(37, 34);
icon.iconAnchor = new GPoint(10, 34);


function usePointFromPostcode(postcode, callbackFunction) {
	
	localSearch.setSearchCompleteCallback(null, 
		function() {
			
			if (localSearch.results[0])
			{		
				var resultLat = localSearch.results[0].lat;
				var resultLng = localSearch.results[0].lng;
				var point = new GLatLng(resultLat,resultLng);
				callbackFunction(point);
			}else{
				alert("Postcode not found!");
			}
		});	
		
	localSearch.execute(postcode + ", UK");
}

function dothingswithsleep( part ) {
    if( part == 0 ) {
        alert( "before sleep" );
        setTimeout( function() { dothingswithsleep( 1 ); }, 3000 );
    } else if( part == 1 ) {
        alert( "after sleep" );
    }
}


function SearchPoints(postcode, postcode2) {
	var attempt;
	var pointa;
	var pointb;
	attempt=0;
	localSearch.setSearchCompleteCallback(null, 
		function() {
			
			if (localSearch.results[0])
			{		
				var resultLat = localSearch.results[0].lat;
				var resultLng = localSearch.results[0].lng;
				attempt=attempt+1;
				point = new GLatLng(resultLat,resultLng);
 	            placeMarkerAtPoint(point);
 	            
				if (attempt==1)
				{
				   	pointa=point;

					// Now do the second postcode search
					localSearch.execute(postcode2 + ", UK");
	            }
				else
				{
					pointb=point;
					showPolyLine(pointa,pointb);
	            }
				

			}else{
				alert("Postcode not found!");
			}
		});	

		localSearch.execute(postcode + ", UK");
}


function placeMarkerAtPoint(point)
{
	var marker = new GMarker(point,icon);
	map.addOverlay(marker);

}

function setCenterToPoint(point)
{
	map.setCenter(point, 17);
}

function showPointLatLng(point)
{
	alert("Latitude: " + point.lat() + "\nLongitude: " + point.lng());
}

function showPolyLine(pointa,pointb)
{
	map.clearOverlays();
	var myFancyLine=new GPolyline([pointa,pointb],"#FF0000",5,0.5);
    map.addOverlay(myFancyLine);
        directionsPanel = document.getElementById("my_textual_div");

	var directions = new GDirections(map, directionsPanel);
	var road="from: "+pointa.lat()+","+pointa.lng()+" to: "+pointb.lat()+","+pointb.lng();
    directions.load(road);
    
    directionsDisplay = new google.maps.DirectionsRenderer({
        suppressMarkers: true,
        preserveViewport: true
    });
    directionsDisplay.setMap(map);

    GEvent.addListener(directions,"load", function() {
          var len1=directions.getDistance().meters/1000;
          var len2=directions.getDistance().meters/1600;
          var len3=myFancyLine.getLength()/1000;
          var len4=myFancyLine.getLength()/1600;
          mapInfo1.innerHTML='<b>By Road Mileage:</b><br>Length (kms): '+len1.toFixed(1)+'<br/>Length (miles): '+len2.toFixed(1)+'<br/>Duration: '+directions.getDuration().html+"<br/><br/>";
          mapInfo2.innerHTML='<b>As the Crow Flies Distance:</b><br>Length (kms): '+len3.toFixed(1)+'<br/>Length (miles): '+len4.toFixed(1);
    })
}


function mapLoad() {
	if (GBrowserIsCompatible()) {
		map = new GMap2(document.getElementById("map"));
		
		map.addControl(new GLargeMapControl());
		map.addControl(new GMapTypeControl());
		map.setCenter(new GLatLng(54.622978,-2.592773), 5, G_NORMAL_MAP);

	}
}

function addLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload != 'function') {
    window.onload = func;
  } else {
    window.onload = function() {
      oldonload();
      func();
    }
  }
}

function addUnLoadEvent(func) {
	var oldonunload = window.onunload;
	if (typeof window.onunload != 'function') {
	  window.onunload = func;
	} else {
	  window.onunload = function() {
	    oldonunload();
	    func();
	  }
	}
}

addLoadEvent(mapLoad);
addUnLoadEvent(GUnload);


