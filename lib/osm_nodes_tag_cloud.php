<?php

function osm_nodes_tag_cloud($json){
    
    $black_list = array(); // ignore these tags
    $black_list[] = 'source';
    $black_list[] = 'name';
    $black_list[] = 'width';
    $black_list[] = 'level';
    $black_list[] = 'lcn';
    $black_list[] = 'addr';
    $black_list[] = 'created_by';
    $black_list[] = 'layer';
    $black_list[] = 'ACCURACY'; 
    $black_list[] = 'AREA';
    $black_list[] = 'AREA_ha';
    $black_list[] = 'COMPILER'; 
    $black_list[] = 'COUNCIL'; 
    $black_list[] = 'CREATED'; 
    $black_list[] = 'DESIG';
    $black_list[] = 'DES_REF';
    $black_list[] = 'DES_TITLE';
    $black_list[] = 'LINK';
    $black_list[] = 'PRECISION'; 
    $black_list[] = 'REDESIG'; 
    $black_list[] = 'TYPE';
    $black_list[] = 'X';
    $black_list[] = 'Y';
    $black_list[] = 'wikipedia';
    $black_list[] = 'website';
    $black_list[] = 'naptan';
    $black_list[] = 'url';
    $black_list[] = 'note';
    $black_list[] = 'phone';
    $black_list[] = 'description';
    
    
    $data = json_decode($json);
    $list = array();
    
    foreach($data->elements as $element){
        if(!isset($element->tags)) continue;
        foreach($element->tags as $key => $val){
            
            // ignore the blacklist
            if( in_array($key, $black_list) ) continue;
            
            // we can blacklist by namespace
            if(in_array(substr($key, 0, strpos($key, ':')), $black_list )) continue;
            
            // if the value is 'no' we ignore it
            //if($val == 'no') continue;
            
            // if the value is 'yes' we just use the key
            /*
            if($val == 'yes'){
                osm_add_word($list, $key);
                continue;
            }
            */
            
            osm_add_word($list, "$key:$val");

        }
    
    }
    
    // convert the list into a rendering
    uasort($list, 'osm_cmp');
    
    echo '<div class="osm-tag-cloud">';
    $odd = true;
    $out = '';
    $max = false;
    foreach($list as $word => $score){
        
        // first one is the largest
        if(!$max){
            $max = $score;
        }
        
        // weight is the % of $max to the nearest 10
        $weight = str_pad( round($score / $max, 1) * 10, 2, '0', STR_PAD_LEFT) ;
        
        $pw = str_replace('_', ' ', $word);
        $pw = str_replace(':', ' ', $pw);
        $pw = ucwords($pw);
        $pw = str_replace(' ', '&#8209;', $pw);
        
        $span = '<span class="osm-tag-cloud-' . $weight . '">';
        $span .= $pw;
        $span .= "</span> ";
        
        // alternately add the spans either side 
        // of the middle of the the string.
        if($odd){
            $out = $out . $span;
            $odd = false;
        }else{
            $out = $span . $out;
            $odd = true;
        } 

    }
    echo $out;
    echo '</div>';
    
   // var_dump($list);
}

function osm_cmp($a, $b) {
    if ($a == $b) {
        return 0;
    }
    return ($a > $b) ? -1 : 1;
}


function osm_add_word(&$list, $word){
    if( array_key_exists($word, $list)){
        $list[$word] = $list[$word] + 1;
    }else{
        $list[$word] = 1;
    }
    
}

?>