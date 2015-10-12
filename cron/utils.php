<?php

/* 
    given a point and a distance produce a polygone that approximates a circle 
    for overpass ql language
*/
function get_buffer_polygone_for_point_ql($lon,$lat,$d_metres){
 
 $points = array();
 for($i = 0; $i < 360; $i = $i + 10){
     $points[] = get_point_for_distance($lon,$lat,$d_metres / 1000,$i);
 }
 
 // print_r($points);
 
 $out = "(poly: \"";
 foreach($points as $p){
     $point = array_reverse($p);
     $out .= implode(' ', $point);
     $out .= ' ';
 }
 
 $out .= "\")";
 return $out;
}

/* 
    given a point and a distance produce a WKT polygone that approximates a circle
*/
function get_buffer_polygone_for_point_wkt($lon,$lat,$d_metres){
 
 $points = array();
 for($i = 0; $i < 360; $i = $i + 10){
     $points[] = get_point_for_distance($lon,$lat,$d_metres / 1000,$i);
 }
 
 // print_r($points);
 
 $out = "POLYGON((";
 for($i = 0; $i < count($points); $i++){
     if($i > 0) $out .= ', ';
     $out .= implode(' ', $points[$i]);
 }
 $out .= ', ' . implode(' ', $points[0]); // ends with start point
 $out .= "))";
 
 return $out;
 
}

function get_point_for_distance($long1,$lat1,$d,$angle){
    # Earth Radious in KM
    $R = 6378.14;

    # Degree to Radian
    $latitude1 = $lat1 * (M_PI/180);
    $longitude1 = $long1 * (M_PI/180);
    $brng = $angle * (M_PI/180);

    $latitude2 = asin(sin($latitude1)*cos($d/$R) + cos($latitude1)*sin($d/$R)*cos($brng));
    $longitude2 = $longitude1 + atan2(sin($brng)*sin($d/$R)*cos($latitude1),cos($d/$R)-sin($latitude1)*sin($latitude2));

    # back to degrees
    $latitude2 = $latitude2 * (180/M_PI);
    $longitude2 = $longitude2 * (180/M_PI);

    # 6 decimal for Leaflet and other system compatibility
   $lat2 = round ($latitude2,6);
   $long2 = round ($longitude2,6);

   // Push in array and get back
   $tab[0] = $long2;
   $tab[1] = $lat2;
   return $tab;
}



?>