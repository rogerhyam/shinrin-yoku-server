
/*
    kicking off jquery 
*/
$( document ).ready(function() {

    var geojsonFormat = new ol.format.GeoJSON();
    var geojsonLoader = function(extent, resolution, projection) {

        var bottomLeft = ol.proj.transform(ol.extent.getBottomLeft(extent), 'EPSG:3857', 'EPSG:4326');
        var topRight = ol.proj.transform(ol.extent.getTopRight(extent), 'EPSG:3857', 'EPSG:4326');
        
        var url = 'geojson.php'
          + '?left='  + wrapLon(bottomLeft[0])
          + '&right=' + wrapLon(topRight[0])
          + '&top='   + topRight[1]
          + '&bottom=' + bottomLeft[1]
          + '&resolution=' + resolution;
       
        $.ajax(url).then(function(response) {
          var features = geojsonFormat.readFeatures(response, {featureProjection: 'EPSG:3857'});
          geojsonSource.addFeatures(features);
        });
    
    }
    
    var geojsonSource = new ol.source.Vector({
      strategy: ol.loadingstrategy.bbox,
      loader: geojsonLoader
    });
    
    var geojsonLayer = new ol.layer.Vector({
      source: geojsonSource
    });

    var osmLayer = new ol.layer.Tile({
      source: new ol.source.OSM()
    });
    
    /**
     * Create an overlay to anchor the popup to the map.
     */
    var overlay = new ol.Overlay(/** @type {olx.OverlayOptions} */ ({
      element: $('#popup')[0],
      autoPan: true,
      autoPanAnimation: {
        duration: 250
      }
    }));
    
    var center = [
      parseFloat($('#map-canvas').data('tb-center-lon')),
      parseFloat($('#map-canvas').data('tb-center-lat'))
    ];

    var map = new ol.Map({
    target: 'map-canvas',
        layers: [osmLayer, geojsonLayer],
        view: new ol.View({
            center: ol.proj.transform(center, 'EPSG:4326', 'EPSG:3857'),
            zoom: 10
        }),
        overlays: [overlay],
        controls: ol.control.defaults({
            attributionOptions: /** @type {olx.control.AttributionOptions} */ ({
            collapsible: false
        })
        }),
    });
    
    // the geojsonLayer only fires change when it finishes loading
    geojsonLayer.on('change', function(evt){
        
        console.log("geojson loaded");
        
        if($('#map-canvas').data('tb-zoom')){
          console.log( $('#map-canvas').data('tb-zoom') );
          var z = $('#map-canvas').data('tb-zoom');
          $('#map-canvas').data('tb-zoom', false); // only once
          map.getView().setZoom(z);
        }
        
        // if we have been passed an id then try and load it
        if( $('#map-canvas').data('tb-survey') ){
          var survey_id = $('#map-canvas').data('tb-survey');
          $('#map-canvas').data('tb-survey', false); // only once
          var f = geojsonSource.getFeatureById(survey_id);
          if(f){
            showPopup(f);
          }
        }
        
        
    });
    
    map.on('singleclick', function(evt) {
      displayFeatureInfo(evt);
    });
    
    $('#popup-closer').on('click', function(){
        overlay.setPosition(undefined);
        $(this).blur();
        return false;
    });
    
    var displayFeatureInfo = function(evt){
      
      var features = [];
      map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
        features.push(feature);
      });
      
      // populate the overlay
      if(features.length > 0){
        
        var hidden = false;
        if(features.length > 1){
          hidden = features.length -1;
        }
        
        showPopup(features[0], hidden);
        
      }
      
    };
    
    var showPopup = function(feature, hidden){
      
      var html = "<strong>" + feature.get('title') + "</strong>";
        html += "<hr/>";
        html += feature.get('description');
    
        if(hidden){
          html += "<hr/>";
          html += "<p>And "+ hidden + " others</p>";
        }
    
        $('#popup-content').html( html );  
      
        // show the overlay
        var coordinate = feature.getGeometry().getCoordinates();
        overlay.setPosition(coordinate);
    }
   
});

function wrapLon(value) {
  var worlds = Math.floor((value + 180) / 360);
  return value - (worlds * 360);
}