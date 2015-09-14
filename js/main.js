
/*
    kicking off jquery 
*/
$( document ).ready(function() {
   
    // initialise the map
    var kmlLayer = new ol.layer.Vector({
            source: new ol.source.Vector({
            url: 'kml.php',
            format: new ol.format.KML()
        })
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
        layers: [osmLayer, kmlLayer ],
        view: new ol.View({
            center: ol.proj.transform(center, 'EPSG:4326', 'EPSG:3857'),
            zoom: $('#map-canvas').data('tb-zoom')
        }),
        overlays: [overlay],
        controls: ol.control.defaults({
            attributionOptions: /** @type {olx.control.AttributionOptions} */ ({
            collapsible: false
        })
        }),
    });
    
    // do we have a default survey flagged to load?
    var survey_id = $('#map-canvas').data('tb-survey');
    if(survey_id){
      var source = kmlLayer.getSource();
      source.on('addfeature', function(evt){
          if(evt.feature.getId() == survey_id){
            console.log(evt.feature);
            showPopup(evt.feature);
          }
      });
    }
    
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
      
      var html = "<strong>" + feature.get('name') + "</strong>";
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