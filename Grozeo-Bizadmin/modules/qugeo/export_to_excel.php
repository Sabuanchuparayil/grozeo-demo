<?php

header('Content-type: application/ms-excel');
header("Content-Disposition: attachment;filename=\"softwareupdate.xls\"");
header("Cache-Control: max-age=0");
$o = new stdClass();

  //$qry = "";

//$data = $db->getMultipleData($qry, true);
$nodb = new \cgoDynamiteDB();
    $arrAPI = array();
    $arrAPI['PartitionKey'] = array('col' => 'apikey',  'val' => $_POST['apikey'], 'oper' => '=');
    $arrAPI['IndexName'] = 'apikey-index';
    $arrAPI['queryAttributes'] = array('longitude', 'latitude', 'tstamp');
    $rsno = $nodb->query('QugeoEventGeoLocations', $arrAPI, 'query');
    $arr = array();
    if (isset($rsno) && count($rsno) > 0) {
        foreach ($rsno as $value) {
            array_push($arr, array('Latitude' => $value['latitude'], 'Longitude' => $value['longitude'], 'tstamp' => $value['tstamp']));
        }
        finascop_aasort($arr, 'tstamp');
    }
    $polyLine = json_encode($arr);

    
 if(!empty($polyLine)){
     $output = '<table border="1">

                <tr>
                  <th width="50px;">Sl No</th>
                    <th width="250px;">Time</th>
                    <th width="250px;">Application Time</th>
                    <th width="150px;">Distance</th>
                    <th width="100px;">Latitude</th>
                    <th width="150px;">Longitude</th>
                    
                     
                </tr>';
     $i=1;
     foreach($polyLine as $key => $singleItem){
          $output .= '<tr>
                 <td align="center">'.$i.'</td>
                <td>'.$singleItem['tstamp'].'</td>
                 <td>'.$singleItem['apptime'].'</td>
                 <td>'.$singleItem['disttravled'].'</td>
                <td>'.$singleItem['Latitude'].'</td>
                <td>'.$singleItem['Longitude'].'</td>
                
                

            </tr>';
         $i++; 
     }
    
          $output .= '</table>';
          echo $output;
 }







?>
