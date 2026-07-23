<?php
namespace App\Traits\Driver;
use Illuminate\Support\Collection;
trait CommonTrait
{
    function sortByNearestLatLongPoints($geoData, $lat, $long, $returnNearestOnly = false)
    {
        $geoCollection = collect($geoData);

        // Calculate the difference for each item in the collection
        $sortedCollection = $geoCollection->map(function ($item) use ($lat, $long) {
        $difference = abs(floatval($item['latitude']) - $lat) + abs(floatval($item['longitude']) - $long);
        return [
            'difference' => $difference,
            'data' => $item,
        ];
        });

        // Sort the collection by the calculated difference
        $sortedCollection = $sortedCollection->sortBy('difference');

        // Retrieve the sorted data
        $sortedData = $sortedCollection->pluck('data')->toArray();

        // Return either the nearest item or the full sorted list based on $returnNearestOnly flag
        return $returnNearestOnly ? [$sortedData[0]] : $sortedData;
    }
    public function finascop_aasort(&$array, $key)
    {
        $sorter = [];
        $ret = [];
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[] = $array[$ii];
        }
        $array = $ret;
    }
    public function curlGetRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Referer: ' . config('constant.REQUEST_HEADER')));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response === false) {
            throw new \Exception('Failed to fetch data using cURL');
        }
        
        return $response;
        
    }
   
}
