<?php
require_once(ROOT . '/../rpc/lib.php');
require_once(ROOT . '/../rpc/config.php');

function getMap()
{
		global $db;

		$accHeads = $db->getMultipleData('select psof_id,concat(postoffice, ", " ,(SELECT district.dst_Name FROM ' . DB_PREFIX . '1.district WHERE district.dst_Id = postoffice.dst_id) , ", Kerala ") as postoffice ,pincode from ' . DB_PREFIX . '1.postoffice where psof_lati = ""  ', TRUE);	
     foreach ($accHeads as $k => $v) {

		
		$url = "https://maps.googleapis.com/maps/api/geocode/json?key=" . GOOGLE_MAP_API_KEY . "&address="  . rawurlencode ($v['postoffice'])   ;

        $opts = array(
            CURLOPT_URL => $url,
            CURLINFO_CONTENT_TYPE => "text/xml",
            CURLOPT_BINARYTRANSFER => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);		

		$googledata = json_decode(stripslashes($data), TRUE);
		//print_r($googledata['results'][0]['formatted_address']);		
		if ($googledata['status']=="OK"){
			$lat = $googledata['results'][0]['geometry']['location']['lat'];
			$lng = $googledata['results'][0]['geometry']['location']['lng'];
			$address = $googledata['results'][0]['formatted_address'];			
			//echo $lat . "-" .$lng;
			$geocord = array('psof_lati'=>$lat,'psof_long'=>$lng,'google_formatted_address'=>$address);
			$status = $db->perform(DB_PREFIX.'1.postoffice', $geocord, 'update', 'psof_id=' . $v['psof_id']);
		}
		else{
			//echo $googledata['status'];
			$missed = $missed . "|" . rawurlencode ($v['postoffice'] . " P.O") . "," . $v['pincode'];
			//exit;			
		}
	}
	
	echo $missed;
	
		
}