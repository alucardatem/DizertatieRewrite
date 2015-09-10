<?php
/**
 * Created by PhpStorm.
 * User: alucardatem
 * Date: 06.09.2015
 * Time: 03:12
 */

namespace WifiCap;


class MapGenerator
{

    function generateMap($DataArray)
    {

        $xml = '<?xml version="1.0" encoding="UTF-8"?> <kml xmlns="http://earth.google.com/kml/2.2"> <Document> <Style id=\"embassy3887bemedium\"><IconStyle><Icon><href>https://api.tiles.mapbox.com/v3/marker/pin-m-embassy+3887be.png</href></Icon></IconStyle><hotSpot xunits=\"fraction\" yunits=\"fraction\" x=\"0.5\" y=\"0.5\"></hotSpot></Style>';
        $DataArray = json_decode($DataArray, true);
        //print_r($DataArray);
        foreach ($DataArray as $key => $record) {
            if (!isset($record["AP_Mac"])) {
                $record["AP_Mac"] = $record["Station_Mac"];
            }
            $xml .= '<Placemark><name>' . $record["Network_Name"] . '</name><Point><coordinates>' . $record["lng"] . ',' . $record["lat"] . '</coordinates></Point><ExtendedData><Data name=\"id\">' . $record["AP_Mac"] . '</Data><Data name=\"marker-color\">#3887be</Data><Data name=\"marker-symbol\">embassy</Data></ExtendedData><styleUrl>#embassy3887bemedium</styleUrl></Placemark>';
        }
        $xml .= '</kml>';
        return $xml;

    }

    function javascriptKMLMapDisplay($KML)
    {
        $test = "";
        if ($KML == "") {
            $test = "geocoder.query('Iasi,Romania',showMap);

            function showMap(err, data) {
    // The geocoder can return an area, like a city, or a
    // point, like an address. Here we handle both cases,
    // by fitting the map bounds to an area or zooming to a point.

        console.log(data);
        map.setView([data.latlng[0], data.latlng[1]], 10);

}";
        }


        return "<script type='text/javascript'>
L.mapbox.accessToken = 'pk.eyJ1IjoiYWx1Y2FyZGF0ZW0iLCJhIjoiZmJmNjk4NDMxY2QzNzJlZjc3MTNkYzQ3ZjhiN2RjNmIifQ.IqMqdNd5MbyWdLVdiHbFbw';
var geocoder = L.mapbox.geocoder('mapbox.places');
var map = L.mapbox.map('map', 'mapbox.streets');
" . $test . ";
var runLayer = omnivore.kml.parse('" . $KML . "').addTo(map);
map.fitBounds(runLayer.getBounds());
</script>";

    }


}
