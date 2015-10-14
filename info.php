<?php
    
    require_once('config.php');

    if(isset($_GET['survey'])){
        $survey_id = $_GET['survey'];
        if(strlen($survey_id) > 40) exit;
    }else{
        echo 'No survey id specified!';
        exit;
    }
    
    $response = $mysqli->query("SELECT * FROM submissions WHERE survey_id = '$survey_id'");
    if($response->num_rows == 1){
        $row = $response->fetch_assoc();
        $survey = json_decode($row['survey_json']);
        $photo = $row['photo'];
        $submission_id = $row['id'];
        // fixme: we could get the surveyor and modified info here
    }else{
        echo "Survey $survey_id not found";
        exit;
    }
    
?>
<div class="tenbreaths-survey-info">
    <div class="tenbreaths-survey-info-float">
    
<?php
    if($photo){
      echo '<img src="/data/'. $photo .'" />';   
    }
    
    // the OSM tag cloud
    $response = $mysqli->query("SELECT * FROM au_osm_nodes WHERE submission_id = $submission_id");
    if($response->num_rows == 1){
        include_once('lib/osm_nodes_tag_cloud.php');
        $row = $response->fetch_assoc();
        osm_nodes_tag_cloud($row['raw']);
    }
?>
    </div>
    
<h3>Survey Information</h3>
<p style="color: orange;">This is work in progress!</p>
<p>
    <?php
        echo $survey->geolocation->display_string;
        // var_dump($survey);
    ?>
</p>

<h3>OpenStreetMap</h3>
<p><strong>Address: </strong>
<?php
    $response = $mysqli->query("SELECT * FROM au_osm_reverse_geocode WHERE submission_id = $submission_id");
    if($response->num_rows == 1){
        
        $row = $response->fetch_assoc();
        $address = json_decode($row['raw']);
        
        echo '<a target="_new" href="';
        echo "http://www.openstreetmap.org/#map=16/" . $address->lat . "/". $address->lon ."&layers=C";
        echo '" title="' .  $address->licence  . '">';
        echo $address->display_name;
        echo '</a>';
        
    }else{
        echo "No address found";
    }
    
?>
</p>

<h3>National Biodiversity Network</h3>
<p>All occurrence records from within 500m of this point.</p>
<?php
    $response = $mysqli->query("SELECT * FROM au_nbn_point_buffer WHERE submission_id = $submission_id");
    if($response->num_rows == 1){
        $row = $response->fetch_assoc();
        $nbn_data = json_decode($row['raw']);
        
        // first output the totals.
        echo '<p><strong>Total species: </strong>'. $row['taxon_count'].'</p>';
        echo '<p><strong>Total records: </strong>'. $row['observation_count'].'</p>';
        
        // create a rendering friendly array
        $render = array();
        foreach($nbn_data as $ob){
            if(!isset($render[$ob->taxon->taxonOutputGroupName])){
                $sum = new stdClass();
                $sum->taxonCount = 1;
                $sum->observationCount = $ob->querySpecificObservationCount;
                $sum->observations[] = $ob;
                $render[$ob->taxon->taxonOutputGroupName] = $sum;
            }else{
                $sum = $render[$ob->taxon->taxonOutputGroupName];
                $sum->taxonCount++;
                $sum->observationCount += $ob->querySpecificObservationCount;
                $sum->observations[] = $ob;
            }
        }
        ksort($render);
        
        foreach($render as $n => $data){
            echo '<p class="nbn-outgroup-taxon">';
            echo '<strong>'. $n . ': </strong>';
            echo $data->taxonCount . ' taxa ' . $data->observationCount . ' records.';
            echo '</p>';
        }
        
    }else{
        echo "No nbn found";
    }
?>
</div>