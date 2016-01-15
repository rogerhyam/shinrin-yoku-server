/*
    Here we define the different combinations of layers,
    centres and zoom levels that people can pick from
*/

/* global ol */

var mapsConfig = {};
mapsConfig.baseLayers = {};
mapsConfig.layers = {};
mapsConfig.maps = {};

/*
    B A S E  L A Y E R S 
*/

// openstreetmap baseLayer
mapsConfig.baseLayers.osm = {
    title: 'OpenStreetMap',
    layer: new ol.layer.Tile({
      source: new ol.source.OSM()
    })  
};

// bing baselayer
mapsConfig.baseLayers.bing = {
  
  title: "MS Bing",
  layer: new ol.layer.Tile({
    visible: true,
    preload: Infinity,
    source: new ol.source.BingMaps({
      key: 'Akv0MSGoQHoH5BNkSQLK5z7ioZBU3xrWZeSKdtODUh1UYj7j_M6Dn_dC--kmO69h',
      crossOrigin: true,
      imagerySet: [
              'Road',
              'Aerial',
              'AerialWithLabels',
              'collinsBart',
              'ordnanceSurvey'
            ]
    })
  }),
};

mapsConfig.baseLayers.mapQuest = {
    title: "MapQuest AerialWithLabels",
    layer: new ol.layer.Group({
    style: 'AerialWithLabels',
    visible: true,
    layers: [
      new ol.layer.Tile({
        source: new ol.source.MapQuest({layer: 'sat'})
      }),
      new ol.layer.Tile({
        source: new ol.source.MapQuest({layer: 'hyb'})
      })
    ]
  })
    
};

/*
    M A P S 
*/

// Map: Edinburgh
mapsConfig.maps.edinburgh = {
 title: 'Edinburgh',
 zoom: 10,
 center: [-3.210008, 55.964747],
 default: true,
 baseLayer: mapsConfig.baseLayers.osm,
 layers: {}
};

mapsConfig.maps.scotland = {
 title: 'Scotland',
 zoom: 6,
 center: [-4, 56],
 default: false,
 baseLayer: mapsConfig.baseLayers.mapQuest,
 layers: {}
};

/*
    L A Y E R S
*/

// - S N H National Nature Reserves   -
var snhNationReservesSource = new ol.source.TileWMS({
 // url: 'http://mapgateway.snh.gov.uk/ServicesWMS/greenspaceWMS/MapServer/WMSServer',
  url: 'http://mapgateway.snh.gov.uk/ServicesWMS/SNH_Protected_Sites/MapServer/WMSServer',
  // url: 'http://mapgateway.snh.gov.uk/ServicesWMS/SNH_Landscape/MapServer/WMSServer',
  //url: 'http://mapgateway.snh.gov.uk/ServicesWMS/SNH_Habitats_and_Species/MapServer/WMSServer',
  params: {
    'LAYERS': '6', 
    'TILED': true,
    'CRS': 'EPSG:3857'
  },
  serverType: 'geoserver'});
mapsConfig.layers.snhNationalReserves = new ol.layer.Tile({source: snhNationReservesSource});

// add it to the edinburgh map
mapsConfig.maps.edinburgh.layers.snhNationalReserves = {
    layer: mapsConfig.layers.snhNationalReserves,
    title: "SNH National Res.",
    defaultOn: true
};

// add it to the scotland map
mapsConfig.maps.scotland.layers.snhNationalReserves = {
    layer: mapsConfig.layers.snhNationalReserves,
    title: "SNH National Res.",
    defaultOn: true
};



// - S N H Local Nature Reserves   -
var snhLocalReservesSource = new ol.source.TileWMS({
 // url: 'http://mapgateway.snh.gov.uk/ServicesWMS/greenspaceWMS/MapServer/WMSServer',
  url: 'http://mapgateway.snh.gov.uk/ServicesWMS/SNH_Protected_Sites/MapServer/WMSServer',
  // url: 'http://mapgateway.snh.gov.uk/ServicesWMS/SNH_Landscape/MapServer/WMSServer',
  //url: 'http://mapgateway.snh.gov.uk/ServicesWMS/SNH_Habitats_and_Species/MapServer/WMSServer',
  params: {
    'LAYERS': '8', 
    'TILED': true,
    'CRS': 'EPSG:3857'
  },
  serverType: 'geoserver'});
mapsConfig.layers.snhLocalReserves = new ol.layer.Tile({source: snhLocalReservesSource});

// add it to the edinburgh map
mapsConfig.maps.edinburgh.layers.snhLocalReserves = {
    layer: mapsConfig.layers.snhLocalReserves,
    title: "SNH Local Res",
    defaultOn: true
};

// add it to the scotland map
mapsConfig.maps.scotland.layers.snhLocalReserves = {
    layer: mapsConfig.layers.snhLocalReserves,
    title: "SNH Local Res",
    defaultOn: true
};

// - S N H SSSI   -
mapsConfig.layers.snhSSSI = new ol.layer.Tile({source: 
 new ol.source.TileWMS({
 // url: 'http://mapgateway.snh.gov.uk/ServicesWMS/greenspaceWMS/MapServer/WMSServer',
  url: 'http://mapgateway.snh.gov.uk/ServicesWMS/SNH_Protected_Sites/MapServer/WMSServer',
  //url: 'http://mapgateway.snh.gov.uk/ServicesWMS/SNH_Habitats_and_Species/MapServer/WMSServer',
  params: {
    'LAYERS': '2', 
    'TILED': true,
    'CRS': 'EPSG:3857'
  },
  serverType: 'geoserver'})
});

// add it to the edinburgh map
mapsConfig.maps.edinburgh.layers.snhSSSI = {
    layer: mapsConfig.layers.snhSSSI,
    title: "SNH SSSI",
    defaultOn: false
};

// - S N H SACs   -
mapsConfig.layers.snhSACs = new ol.layer.Tile({source: 
 new ol.source.TileWMS({
  url: 'http://mapgateway.snh.gov.uk/ServicesWMS/SNH_Protected_Sites/MapServer/WMSServer',
  params: {
    'LAYERS': '4', 
    'TILED': true,
    'CRS': 'EPSG:3857'
  },
  serverType: 'geoserver'})
});

// add it to the edinburgh map
mapsConfig.maps.edinburgh.layers.snhSACs = {
    layer: mapsConfig.layers.snhSACs,
    title: "SNH SACs",
    defaultOn: false
};

// - S N H SPAs   -
mapsConfig.layers.snhSPAs = new ol.layer.Tile({source: 
 new ol.source.TileWMS({
  url: 'http://mapgateway.snh.gov.uk/ServicesWMS/SNH_Protected_Sites/MapServer/WMSServer',
  params: {
    'LAYERS': '3', 
    'TILED': true,
    'CRS': 'EPSG:3857'
  },
  serverType: 'geoserver'})
});

// add it to the edinburgh map
mapsConfig.maps.edinburgh.layers.snhSPAs = {
    layer: mapsConfig.layers.snhSPAs,
    title: "SNH SPAs",
    defaultOn: false
};

// - S N H SPAs   -
mapsConfig.layers.snhWildness = new ol.layer.Tile({source: 
        new ol.source.TileWMS({
         url: 'http://mapgateway.snh.gov.uk/ServicesWMS/SNH_Landscape/MapServer/WMSServer',
            params: {
                'LAYERS': '3', 
                'TILED': true,
                'CRS': 'EPSG:3857'
            },
            serverType: 'geoserver'
        }),
        opacity: 0.5
});

// add it to the edinburgh map
mapsConfig.maps.edinburgh.layers.snhWildness = {
    layer: mapsConfig.layers.snhWildness,
    title: "SNH Wildness",
    defaultOn: false
};
  



/*
    U T I L  M E T H O D S
*/

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

mapsConfig.setMap = function(newMap){
  
  console.log('setMap');
  
  // flag map as current one
  $('#maps-sub-menu li').each(function(){

     var m = $(this).find('a').data('map');
     if(m == newMap){
         $(this).find('a').html(m.title + ' &#10004;');
     }else{
         $(this).find('a').html(m.title);
     }
     
  });
  
  // set up the layers menu
  mapsConfig.populateLayersMenu(newMap.layers);
  
  // remove all the current layers - provided they are one of our
  // configured ones and not something added later - like tenbreaths
  for (var l in mapsConfig.layers) {
        if (mapsConfig.layers.hasOwnProperty(l)) {
            mapsConfig.map.removeLayer(l.layer);     
        }
  }

  // add in the default base layer for the map.
  //console.log(newMap);
  mapsConfig.setBaseLayer(newMap.baseLayer);
  //mapsConfig.map.getLayers().insertAt(0, newMap.baseLayer.layer);

  // travel to the new view
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
    mapsConfig.map.getView().setCenter(ol.proj.transform(newMap.center, 'EPSG:4326', 'EPSG:3857') );
    mapsConfig.map.getView().setZoom(newMap.zoom);
  
  // add in any layers that are on by default
  // fixme

};

mapsConfig.setBaseLayer = function(layer){
    console.log(layer);
    mapsConfig.map.removeLayer(mapsConfig.map.getLayers().item(0));
    mapsConfig.map.getLayers().insertAt(0, layer.layer);
    
    // fixme: put a tick by the correct menu
    $('#base-layers-sub-menu li').each(function(){
        
        console.log($(this));

     var l = $(this).find('a').data('layer');
     if(l == layer){
         $(this).find('a').html(l.title + ' &#10004;');
     }else{
         $(this).find('a').html(l.title);
     }
     
  });
    
};

mapsConfig.populateLayersMenu = function(layers){

    console.log('set layers');

    var baseLayersMenu = $('#layers-sub-menu').children().first().detach();
    $('#layers-sub-menu').empty();
    $('#layers-sub-menu').append(baseLayersMenu);
    
    for (var l in layers) {
        if (layers.hasOwnProperty(l)) {
            
            var thisLayer = layers[l];
            console.log(thisLayer);
            
            var li = $('<li></li>');
            $('#layers-sub-menu').append(li);
            var a = $('<a></a>');
            li.append(a);
            a.attr('href', '#');
            a.html(thisLayer.title);
            a.data('layer', thisLayer);
            a.on('click', function(){
               mapsConfig.toggleLayer($(this).data('layer'), $(this)); 
            });

        }
    }
    
};

mapsConfig.toggleLayer = function(layer, anchor){
    
  mapsConfig.map.getLayers().forEach(function(l,i){
      if(l == layer.layer){
          mapsConfig.map.removeLayer(l);
          anchor.html(layer.title);
          layer = false;
      }
  });
  
  if(layer){
      mapsConfig.map.getLayers().insertAt(1, layer.layer);
      anchor.html(layer.title +  ' &#10004;');
  }
    
  
  console.log(layer);  
};

mapsConfig.populateMapsMenu = function(maps){
    
    $('#maps-sub-menu').empty();
    for (var m in maps) {
        if (maps.hasOwnProperty(m)) {

            var thisMap = maps[m];
            
            var li = $('<li></li>');
            $('#maps-sub-menu').append(li);
            var a = $('<a></a>');
            li.append(a);
            a.attr('href', '#');
            a.html(thisMap.title);
            a.data('map', thisMap);
            a.on('click', function(){
               mapsConfig.setMap($(this).data('map')); 
            });
            
        }
    }
    
};

mapsConfig.populateBaseLayerMenu = function(baseLayers){

    $('#base-layers-sub-menu').empty();
    for (var bl in baseLayers) {
        if (baseLayers.hasOwnProperty(bl)) {

            var thisLayer = baseLayers[bl];
            
            var li = $('<li></li>');
            $('#base-layers-sub-menu').append(li);
            var a = $('<a></a>');
            li.append(a);
            a.attr('href', '#');
            a.html(thisLayer.title);
            a.data('layer', thisLayer);
            a.on('click', function(){
               console.log('banana');
               mapsConfig.setBaseLayer($(this).data('layer')); 
            });
            console.log(a);
            
        }
    }
    
};


$( document ).ready(function() { 
  
   // we need a map!
  
   mapsConfig.map = new ol.Map({
        target: 'map-canvas',
        layers: [], // no layers
        view: new ol.View({
             center: ol.proj.transform([-3.210008, 55.964747], 'EPSG:4326', 'EPSG:3857'),
             zoom: 10
        })
    });// end of map layer
  
   mapsConfig.populateMapsMenu(mapsConfig.maps);
   mapsConfig.populateBaseLayerMenu(mapsConfig.baseLayers);
  
   // set up the default map - fixme: check for map in session?
   
   for (var m in mapsConfig.maps) {
        
        console.log(m);
        if (mapsConfig.maps.hasOwnProperty(m)) {
            if(mapsConfig.maps[m].default){
                mapsConfig.setMap(mapsConfig.maps[m]);
                break;
            }
        }
   }
   
   $('.popup-page-closer').on('click', function(){
     $(this).parent().hide('slow');
   });
});