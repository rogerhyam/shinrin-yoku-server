
/*
    kicking off jquery 
*/
/* global ol */
/* global mapsConfig */
/* global $ */

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
          
          // if the cluster contains features that belong to the 
          // selected user then they are highlighted color
          var containsUserPoint = false;
          for(var i = 0; i < feature.get("features").length; i++){
            var f = feature.get("features")[i];
            if(f.get('user_key') == $('#map-canvas').data('tb-user-key')){
                containsUserPoint = true;
                break;
            }
          }
          
          var strokeColor = '#0b222d'; // dark blue
          var fillColor =  '#3399CC'; // blue
          if(containsUserPoint){
            strokeColor = '#492312'; // dark orange
            fillColor = '#cc6133'; // orange
          }
          
          // if the cluster is exclusively private then the
          // shape is highlight
          var notPublic = true;
          for(var i = 0; i < feature.get("features").length; i++){
            var f = feature.get("features")[i];
            if(f.get('public_visible') == 1){
                notPublic = false;
                break;
            }
          }
          
          var featureImage;
          if(notPublic){
             featureImage = new ol.style.RegularShape({
                  fill: new ol.style.Fill({
                  color: fillColor
                }),
                  stroke:  new ol.style.Stroke({
                  color: strokeColor
                }),
                  points: 4,
                  radius: 10,
                  angle: Math.PI / 4
              });
          }else{
            featureImage = new ol.style.Circle({
                radius: 10,
                stroke: new ol.style.Stroke({
                  color: strokeColor
                }),
                fill: new ol.style.Fill({
                  color: fillColor
                })
              });
          }
          
          
          var size = feature.get('features').length;
          
          var styleId = size + '-' + containsUserPoint + '-' + notPublic;
          var style = styleCache[styleId];
          if (!style) {
            style = [new ol.style.Style({
              image: featureImage,
              text: new ol.style.Text({
                text: size.toString(),
                fill: new ol.style.Fill({
                  color: '#fff'
                })
              })
              
            })];
      
            styleCache[styleId] = style;
          
          } // end no style
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
      
      var html = "<strong>" + feature.get('title') + "</strong>";
        
        if(feature.get('description') && feature.get('description').length > 0){
          html += "<hr/>";
          html += feature.get('description');
        }else{
          html += '<p/>';
        }

        $('#popup-content').html( html );
        
        // set the facebook & other share url
        $('#popup').data('href', $('#popup').data('tenbreaths-base-url') + 'survey-' + feature.getId() );
        $('#popup').data('survey_id', feature.getId());
        
        // show the overlay
        var coordinate = feature.getGeometry().getCoordinates();
        overlay.setPosition(coordinate);
        
        // also set the location data so we can launch a google maps link
        var longLat = ol.proj.transform(coordinate, 'EPSG:3857', 'EPSG:4326');
        $('#popup').data('longitude', longLat[0]);
        $('#popup').data('latitude', longLat[1]);
        $('#popup').data('zoom', mapsConfig.map.getView().getZoom());
        
        // also set the height depending on if there is an image or not
        if(feature.get('has_image')){
          $('#popup').addClass('popup-has-image');
          $('#popup').removeClass('popup-lacks-image');
        }else{
          $('#popup').addClass('popup-lacks-image');
          $('#popup').removeClass('popup-has-image');
        }

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
    
    // link creates mailto
    $('#popup-mail').on('click',function(){
      window.location.href = 'mailto:?subject=Ten Breaths Map&body=' + $('#popup').data('href');
    });
    
    // report a concern
    $('#popup-report').on('click',function(){
      var body = "I have a concern regarding this contribution:\n\n" + $('#popup').data('href');
      body = encodeURIComponent(body);
      window.location.href = 'mailto:?to=r.hyam@rbge.org.uk&subject=Ten Breaths Map: Report Concern&body=' + body;
    });
    
    // link goes to google maps
    $('#popup-google-maps').on('click',function(){
      
      // http://maps.google.com/maps?&z={INSERT_MAP_ZOOM}&mrt={INSERT_TYPE_OF_SEARCH}&t={INSERT_MAP_TYPE}&q={INSERT_MAP_LAT_COORDINATES}+{INSERT_MAP_LONG_COORDINATES}

      var gmapUri = 'http://maps.google.com/maps'
        + '?z=' +  $('#popup').data('zoom')
        + '&mrt=yp'
        + '&t=k'
        + '&q=' +  $('#popup').data('latitude')
        + '+' + $('#popup').data('longitude');
      
        window.open(gmapUri, '_blank');
        
    });

    
    
    // if there is a popup page set then open that by default
    if($('#map-canvas').data('tb-pop-page')){
      mapsConfig.showPopupPage($('#map-canvas').data('tb-pop-page'));
    }
    
   
}); // end doc ready

/*
    U T I L  M E T H O D S
*/

mapsConfig.zoomToCurrentLocation = function(){
  
        $('.popup-page').hide('slow'); // hide any open pages
        
        if(navigator.geolocation){
          
          $('#menu-nearby-link').html("Bear with...");
          
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
            
              $('#menu-nearby-link').html("Nearby");
            
            },
            function(error){
              $('#menu-nearby-link').html("Nearby");
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
