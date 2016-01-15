
/*
    kicking off jquery 
*/
/* global ol */
/* global mapsConfig */

var mapsConfig = {};
mapsConfig.baseLayers = {};
mapsConfig.layers = {};
mapsConfig.maps = {};

$( document ).ready(function() {
  
    // - create a map
    mapsConfig.map = new ol.Map({
      target: 'map-canvas',
      layers: [], // no layers yet add them in a mo
      view: new ol.View({
         center: ol.proj.transform([-3.210008, 55.964747], 'EPSG:4326', 'EPSG:3857'), // this is just a default default - should be overwritten below
         zoom: 10 // we zoom in a bit from this depending on how we find their location
      })
    });// end of map layer
    
    // decide on how we centre the map
    // firstly if it is set in the page by the server
    if($('#map-canvas').data('tb-center-lon') && $('#map-canvas').data('tb-center-lat') &&  $('#map-canvas').data('tb-zoom') ){
      var center = [
        parseFloat($('#map-canvas').data('tb-center-lon')),
        parseFloat($('#map-canvas').data('tb-center-lat'))
      ];
      center = ol.proj.transform(center, 'EPSG:4326', 'EPSG:3857');
      console.log(center);
      var zoom = parseInt($('#map-canvas').data('tb-zoom'));
      mapsConfig.map.getView().setCenter(center);
      mapsConfig.map.getView().setZoom(mapsConfig.map.getView().getZoom() + 4 );
      
    }else{
      
      // try and get the last place they were looking at
      var lastViewStateString = localStorage.getItem('last-view-state');
      if(lastViewStateString){
        var lastViewState = JSON.parse(lastViewStateString);
        console.log('lastViewState');
        console.log(lastViewState);
        mapsConfig.map.getView().setCenter(lastViewState.center);
        mapsConfig.map.getView().setResolution(lastViewState.resolution);
      }else{
        console.log("no previous view state");
        mapsConfig.zoomToCurrentLocation();
       // if they are here then we haven't found a location
       // and they will be centered on Edinburgh
      }
      
    }
    
    // base layer is osm
    var osmLayer =  new ol.layer.Tile({
        source: new ol.source.OSM()
    });

    mapsConfig.map.addLayer(osmLayer);
    
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
    
    
    // heat map layer stuff
    
    var heatLayer = new ol.layer.Heatmap({
      source: geojsonSource,
      radius: 10,
      blur: 40,
      opacity: 0.6
    });
   // heatLayer.setOpacity(0.3);
    mapsConfig.map.addLayer(heatLayer);
    
    // cluster layer stuff
    
    var clusterSource = new ol.source.Cluster({
      distance: 20,
      source: geojsonSource
    });
    
    var styleCache = {};
    var clusterLayer = new ol.layer.Vector({
        source: clusterSource,
        style: function(feature, resolution) {
          
          // console.log(feature.get("features")[0].get('title'));
          // console.log(feature.get('title'));
          
          // fixme Change the colour depending on if this is the 
          // user in the local storage or if it is a hidden spot
          
          var size = feature.get('features').length;
          if(size == 1){
            console.log(feature.get('features')[0].get('user_key'));
          }
          
          var style = styleCache[size];
          if (!style) {
            style = [new ol.style.Style({
              image: new ol.style.Circle({
                radius: 12,
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
    
    mapsConfig.map.addLayer(clusterLayer);
    
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
    mapsConfig.map.addOverlay(overlay);

    // the geojsonLayer only fires change when it finishes loading
    geojsonSource.on('change', function(evt){
      
        // if we have been passed an id then try and load it
        if( $('#map-canvas').data('tb-survey') ){
          var survey_id = $('#map-canvas').data('tb-survey');
          $('#map-canvas').data('tb-survey', false); // only once
          var f = geojsonSource.getFeatureById(survey_id);
          if(f){
            showPopup(f);
          }
        }
        
        
    }); // end on change
    
    
    // track map position and zoom
    mapsConfig.map.on('moveend', function(mapEvt){
      var viewStateString = JSON.stringify(mapEvt.frameState.viewState);
      localStorage.setItem('last-view-state', viewStateString);
    });
    
    mapsConfig.map.on('singleclick', function(evt) {
      
      // if the popup is already popped up then just close it 
      if(overlay.getPosition()){
        overlay.setPosition(undefined);
      }
      displayFeatureInfo(evt);

    });
    
    $('#popup-closer').on('click', function(){
        overlay.setPosition(undefined);
        $(this).blur();
        return false;
    });
    
    $('.popup-page-closer').on('click', function(){
     $(this).parent().hide('slow');
    });
    
    var displayFeatureInfo = function(evt){
      
      var clusters = [];
      mapsConfig.map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
        if(layer != clusterLayer) return;
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

/*
    U T I L  M E T H O D S
*/

mapsConfig.zoomToCurrentLocation = function(){
  
    // not having much luck - try their current location
        if(navigator.geolocation){
          console.log('calling for pos');
          navigator.geolocation.getCurrentPosition(
            function(position){
              console.log(position);
              var center = [
                parseFloat(position.coords.longitude),
                parseFloat(position.coords.latitude)
              ];
              center = ol.proj.transform(center, 'EPSG:4326', 'EPSG:3857');
              
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
              mapsConfig.map.getView().setCenter(center);
              mapsConfig.map.getView().setZoom(16);
            
            },
            function(error){
              alert("Sorry. Couldn't obtain your position. Error code: " + error.code);
            }
            );
        }else{
          alert("Sorry. Geolocation services aren't available.");
        }
  
}

mapsConfig.showPopupPage = function(pageId){
  
  if($('#' + pageId).is(":visible")){
    $('#' + pageId).hide('slow');
  }else{
    $('.popup-page').hide('slow'); // hide any open ones
    $('#' + pageId).show('slow'); // show the requested one
  }

};

mapsConfig.wrapLon = function(value) {
  var worlds = Math.floor((value + 180) / 360);
  return value - (worlds * 360);
};
