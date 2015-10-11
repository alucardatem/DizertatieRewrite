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

        $file_name = "kml/" . date("YmdHis");
        $xml = '<?xml version="1.0" encoding="UTF-8"?> <kml xmlns="http://earth.google.com/kml/2.2"> <Document><name>'.$file_name.'</name>    <Style id="west_campus_style"><IconStyle><Icon><href>https://maps.google.com/mapfiles/kml/pushpin/ylw-pushpin.png</href></Icon></IconStyle><BalloonStyle><text>$[video]</text></BalloonStyle></Style>';
        $DataArray = json_decode($DataArray, true);
        //print_r($DataArray);
        foreach ($DataArray as $key => $record) {
            if (!isset($record["AP_Mac"])) {
                $record["AP_Mac"] = $record["Station_Mac"];
            }
            $xml .= '<Placemark><name>' . $record["Network_Name"] . '</name><ExtendedData><Data name="video"><value>' . $record["AP_Mac"] . '</value></Data></ExtendedData><Point><coordinates>' . $record["lng"] . ',' . $record["lat"] . ',0</coordinates></Point><styleUrl>#west_campus_style</styleUrl></Placemark>';
        }
        $xml .= '</Document></kml>';
        $file_name .= ".kml";
        file_put_contents($file_name, $xml);
        return $file_name;

    }

    function javascriptKMLMapDisplay($file_name)
    {


 /*       return "   <script>

function initMap() {
  var map = new google.maps.Map(document.getElementById('map'), {
    zoom: 12,
    center: {lat: 37.06, lng: -95.68}
  });

  var kmlLayer = new google.maps.KmlLayer({
    url: '$file_name',
    suppressInfoWindows: true,
    map: map
  });

  kmlLayer.addListener('click', function(kmlEvent) {
    var text = kmlEvent.featureData.description;
    showInContentWindow(text);
  });

  function showInContentWindow(text) {
    var sidediv = document.getElementById('content-window');
    sidediv.innerHTML = text;
  }
}

    </script>";*/

        return "<script type='text/javascript'>
L.mapbox.accessToken = 'pk.eyJ1IjoiYWx1Y2FyZGF0ZW0iLCJhIjoiZmJmNjk4NDMxY2QzNzJlZjc3MTNkYzQ3ZjhiN2RjNmIifQ.IqMqdNd5MbyWdLVdiHbFbw';
var geocoder = L.mapbox.geocoder('mapbox.places');
var map = L.mapbox.map('map', 'mapbox.streets');
geocoder.query('Iasi,Romania',showMap);
function showMap(err, data) {
    // The geocoder can return an area, like a city, or a
    // point, like an address. Here we handle both cases,
    // by fitting the map bounds to an area or zooming to a point.

        console.log(data);
        map.setView([data.latlng[0], data.latlng[1]], 10);

}
var customLayer = L.geoJson(null, {
    // http://leafletjs.com/reference.html#geojson-style
    style: function(feature) {
        return {
            color: '#f00'
        };
    },

});
var runLayer = omnivore.kml('./$file_name',null, customLayer).on('ready', function() {
        map.fitBounds(runLayer.getBounds());
    })
    .addTo(map);


</script>";

    }


}
