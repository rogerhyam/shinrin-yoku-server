
/*
    kicking off jquery 
*/
/* global ol */
/* global mapsConfig */

$( document ).ready(function() {

    // - T E N  B R E A T H S   L A Y E R -
    
    var geojsonFormat = new ol.format.GeoJSON();
    var geojsonLoader = function(extent, resolution, projection) {

        var bottomLeft = ol.proj.transform(ol.extent.getBottomLeft(extent), 'EPSG:3857', 'EPSG:4326');
        var topRight = ol.proj.transform(ol.extent.getTopRight(extent), 'EPSG:3857', 'EPSG:4326');
        
        var url = 'geojson.php'
          + '?left='  + mapsConfig.wrapLon(bottomLeft[0])
          + '&right=' + mapsConfig.wrapLon(topRight[0])
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
    
    var clusterSource = new ol.source.Cluster({
      distance: 20,
      source: geojsonSource
    });
    
    var styleCache = {};
    var clusterLayer = new ol.layer.Vector({
        source: clusterSource,
        style: function(feature, resolution) {
          var size = feature.get('features').length;
          var style = styleCache[size];
          if (!style) {
            style = [new ol.style.Style({
              image: new ol.style.Circle({
                radius: 10,
                stroke: new ol.style.Stroke({
                  color: '#fff'
                }),
                fill: new ol.style.Fill({
                  color: '#3399CC'
                })
              }),
              text: new ol.style.Text({
                text: size.toString(),
                fill: new ol.style.Fill({
                  color: '#fff'
                })
              })
            })];
            styleCache[size] = style;
          }
          return style;
        }
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
    
    mapsConfig.map.addLayer(clusterLayer);
    mapsConfig.map.addOverlay(overlay);

    // the geojsonLayer only fires change when it finishes loading
    geojsonSource.on('change', function(evt){
        
        console.log("geojson loaded");
        
        if($('#map-canvas').data('tb-zoom')){
          console.log( $('#map-canvas').data('tb-zoom') );
          var z = $('#map-canvas').data('tb-zoom');
          $('#map-canvas').data('tb-zoom', false); // only once
          mapsConfig.map.getView().setZoom(z);
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
    
    mapsConfig.map.on('singleclick', function(evt) {
      displayFeatureInfo(evt);
    });
    
    $('#popup-closer').on('click', function(){
        overlay.setPosition(undefined);
        $(this).blur();
        return false;
    });
    
    var displayFeatureInfo = function(evt){
      
      var clusters = [];
      mapsConfig.map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
        clusters.push(feature);
      });
      
      // if we have clicked on a single cluster
      if(clusters.length > 0 && clusters.length < 2){
        
        // if the cluster only contains a single feature show it
        // otherwize zoom in
        var features = clusters[0].get("features");
        if(features.length == 1){
          showPopup(features[0]);
        }else{
            var duration = 1000;
            var start = +new Date();
            var pan = ol.animation.pan({
              duration: duration,
              source: /** @type {ol.Coordinate} */ (mapsConfig.map.getView().getCenter()),
              start: start
            });
            var zoom = ol.animation.zoom({
              duration: duration,
              resolution: mapsConfig.map.getView().getResolution(),
              start: start
            });
            mapsConfig.map.beforeRender(pan, zoom);
            mapsConfig.map.getView().setCenter(clusters[0].getGeometry().getCoordinates());
            mapsConfig.map.getView().setZoom(mapsConfig.map.getView().getZoom() + 2 );
        }
      }
      
    };
    
    var showPopup = function(feature){
      
      console.log(feature);
      
      var html = "<strong>" + feature.get('title') + "</strong>";
        
        html += "<hr/>";
        html += feature.get('description');
    
        $('#popup-content').html( html );
        
        // set the facebook & other share url
        $('#popup').data('href', $('#popup').data('tenbreaths-base-url') + 'survey-' + feature.getId() );
        $('#popup').data('survey_id', feature.getId());
        
        // show the overlay
        var coordinate = feature.getGeometry().getCoordinates();
        overlay.setPosition(coordinate);
    };
    
    // show fb share window
    $('#popup-fb-share').on('click',function(){
      window.open(
        "https://www.facebook.com/sharer/sharer.php?u=" + $('#popup').data('href'),
        '',
        'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600'
      );
    });
    
    // show twitter share window
    $('#popup-twitter-share').on('click',function(){
      window.open(
        "https://twitter.com/intent/tweet?url=" + encodeURIComponent($('#popup').data('href')) 
            + "&text=" + encodeURIComponent($('#popup strong').html())
            + "&via=tenbreathsmap",
        '',
        'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600'
      );
    });
    
    // link goes to the href
    $('#popup-link').on('click',function(){
      window.location.href = $('#popup').data('href');
    });
    
    // link goes to the href
    $('#popup-info').on('click',function(){
      $('#info-page .content').load('info.php?survey=' + $('#popup').data('survey_id'));
      mapsConfig.showPopupPage('info-page');
    });
   
}); // end doc ready

