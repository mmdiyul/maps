@extends('layout.dashboard-layout')

@section('title', 'Peta')

@section('styles')
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
  <link rel="stylesheet" href="css/headers.css">
  <link rel="stylesheet" href="css/leaflet/leaflet-search.css">
  <link rel="stylesheet" href="css/leaflet/leaflet.draw.css">
  
	<style>
    #mapid {
      height: 100vh; 
    }
    .leaflet-popup-content-wrapper {
      background-color: #555;
      color: #eee;
    }
    .leaflet-popup-tip {
      background-color: #555;
    }
  </style>
@endsection

@section('script')
  <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
  <script src="js/leaflet/leaflet-search.js"></script>
	<script src="js/leaflet/Leaflet.draw.js"></script>
	<script src="js/leaflet/Leaflet.Draw.Event.js"></script>
	<script src="js/leaflet/Toolbar.js"></script>
	<script src="js/leaflet/Tooltip.js"></script>
	<script src="js/leaflet/GeometryUtil.js"></script>
	<script src="js/leaflet/LatLngUtil.js"></script>
	<script src="js/leaflet/LineUtil.Intersect.js"></script>
	<script src="js/leaflet/Polygon.Intersect.js"></script>
	<script src="js/leaflet/Polyline.Intersect.js"></script>
	<script src="js/leaflet/TouchEvents.js"></script>
@endsection

@section('content')
  <header class="p-3 bg-dark text-white">
    <div class="container">
      <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-map" viewBox="0 0 16 16" ">
          <path fill-rule="evenodd" d="M15.817.113A.5.5 0 0 1 16 .5v14a.5.5 0 0 1-.402.49l-5 1a.502.502 0 0 1-.196 0L5.5 15.01l-4.902.98A.5.5 0 0 1 0 15.5v-14a.5.5 0 0 1 .402-.49l5-1a.5.5 0 0 1 .196 0L10.5.99l4.902-.98a.5.5 0 0 1 .415.103zM10 1.91l-4-.8v12.98l4 .8V1.91zm1 12.98 4-.8V1.11l-4 .8v12.98zm-6-.8V1.11l-4 .8v12.98l4-.8z"/>
          <strong style= "margin-right: 10%;">&nbsp; Fi-Maps Kota Bandung</strong>
        </svg>
        <center>
          <div class="text-end">
            <button onclick="showMarker()" type="button" class="btn btn-warning " >Wifi Terdekat</button>
            <button onclick="showPolygon()" type="button" class="btn btn-warning">Area Titik Wifi Lain</button>
            <button onclick="clearMap()" type="button" class="btn btn-warning">Clear All Map</button>
          </div>
        </center>
      </div>
    </div>
  </header>

  <div id="mapid"></div>
@endsection

@section('body-script')
  <script>
    const data = {!! $data !!}
    let currentCoordinate = {
      latitude: 0,
      longitude: 0
    }


    var Esri_WorldTopoMap = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
      attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (Hong Kong), and the GIS User Community'
    });
    var Esri_WorldImagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
      attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
    });
    var OpenStreetMap_BZH = L.tileLayer('https://tile.openstreetmap.bzh/br/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Tiles courtesy of <a href="http://www.openstreetmap.bzh/" target="_blank">Breton OpenStreetMap Team</a>'
    });
    var OpenTopoMap = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
      attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'
    });
    var layerGroup = {
      "Dark Map": Esri_WorldImagery,
      "Ori Map": OpenStreetMap_BZH,
      "Topo Map": OpenTopoMap,
      "World Topo Map": Esri_WorldTopoMap,
    };

    var mymap = L.map('mapid', {
      center: [currentCoordinate.latitude, currentCoordinate.longitude],
      zoom : 6,
      maxZoom:20,
      minZoom: 6,
      zoomControl: false,
      layers:[Esri_WorldTopoMap]
		});
    
    navigator.geolocation.watchPosition((data) => {
      currentCoordinate = data.coords
    })

    navigator.geolocation.getCurrentPosition((data) => {
      currentCoordinate = data.coords
      mymap.flyTo([currentCoordinate.latitude, currentCoordinate.longitude], 15);
      L.marker([currentCoordinate.latitude, currentCoordinate.longitude]).addTo(mymap)
    })

    function showMarker() {
      data.forEach(element => {
        if (element.geojson) {
          const geojson = JSON.parse(element.geojson)
          if (geojson.type == 'Point') {
            var latitude = geojson.coordinates[1];
            var longitude = geojson.coordinates[0];
            var marker = L.marker([latitude, longitude])
            marker.bindPopup(element.nama).addTo(mymap);
          }
        }
      });
    }

    function showPolygon() {
      data.forEach(element => {
        if (element.geojson) {
          const geojson = JSON.parse(element.geojson)
          if (geojson.type == 'Polygon') { 
            var polygon_style = {
              fillColor: 'red',
              fillOpacity: 0.3,
              color: 'red',
              opacity: 0.8,
            };
            L.geoJson(geojson, polygon_style).bindPopup(element.nama).addTo(mymap); 
          }
        }
      })
    }

    function clearMap() {
      
    }
  </script>
@endsection