<?php
/*
[-]d.d, [-]d.d 	Decimal degrees with negative numbers for South and West. 	12.3456, -98.7654
d° m.m′ {N|S}, d° m.m′ {E|W} 	Degrees and decimal minutes with N, S, E or W suffix for North, South, East, West 	12° 20.736′ N, 98° 45.924′ W
{N|S} d° m.m′ {E|W} d° m.m′ 	Degrees and decimal minutes with N, S, E or W prefix for North, South, East, West 	N 12° 20.736′, W 98° 45.924′
d° m' s" {N|S}, d° m' s" {E|W} 	Degrees, minutes and seconds with N, S, E or W suffix for North, South, East, West 	12° 20' 44" N, 98° 45' 55" W
{N|S} d° m' s", {E|W} d° m' s" 	Degrees, minutes and seconds with N, S, E or W prefix for North, South, East, West 	N 12° 20' 44", W 98° 45' 55"
*/

$exif = exif_read_data($filename);
$lon = getGps($exif["GPSLongitude"], $exif['GPSLongitudeRef']);
$lat = getGps($exif["GPSLatitude"], $exif['GPSLatitudeRef']);
var_dump($lat, $lon);

function gps($coordinate, $hemisphere) {
	for ($i = 0; $i < 3; $i++) {
		$part = explode('/', $coordinate[$i]);
		if (count($part) == 1) {
			$coordinate[$i] = $part[0];
		} else if (count($part) == 2) {
			$coordinate[$i] = floatval($part[0])/floatval($part[1]);
		} else {
			$coordinate[$i] = 0;
		}
	}
	list($degrees, $minutes, $seconds) = $coordinate;
	$sign = ($hemisphere == 'W' || $hemisphere == 'S') ? -1 : 1;
	
	// Latitude
	$northing = -1;
	if( $gpsblock['GPSLatitudeRef'] && 'N' == $gpsblock['GPSLatitudeRef'] )
	{
		$northing = 1;
	}
	
	$northing *= defraction( $gpsblock['GPSLatitude'][0] ) + ( defraction($gpsblock['GPSLatitude'][1] ) / 60 ) + ( defraction( $gpsblock['GPSLatitude'][2] ) / 3600 );
	
	// Longitude
	$easting = -1;
	if( $gpsblock['GPSLongitudeRef'] && 'E' == $gpsblock['GPSLongitudeRef'] )
	{
		$easting = 1;
	}
		
	
	//return $sign * ($degrees + $minutes/60 + $seconds/3600);
	return floatval($sign * ($degrees + $minutes/60 + $seconds/3600));
}


// distances


/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
/*::                                                                         :*/
/*::  This routine calculates the distance between two points (given the     :*/
		/*::  latitude/longitude of those points). It is being used to calculate     :*/
/*::  the distance between two locations using GeoDataSource(TM) Products    :*/
/*::                     													 :*/
/*::  Definitions:                                                           :*/
/*::    South latitudes are negative, east longitudes are positive           :*/
/*::                                                                         :*/
/*::  Passed to function:                                                    :*/
/*::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  :*/
/*::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  :*/
/*::    unit = the unit you desire for results                               :*/
/*::           where: 'M' is statute miles                                   :*/
/*::                  'K' is kilometers (default)                            :*/
/*::                  'N' is nautical miles                                  :*/
/*::  Worldwide cities and other features databases with latitude longitude  :*/
/*::  are available at http://www.geodatasource.com                          :*/
/*::                                                                         :*/
/*::  For enquiries, please contact sales@geodatasource.com                  :*/
/*::                                                                         :*/
/*::  Official Web site: http://www.geodatasource.com                        :*/
/*::                                                                         :*/
/*::         GeoDataSource.com (C) All Rights Reserved 2014		   		     :*/
/*::                                                                         :*/
/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
function distance($lat1, $lon1, $lat2, $lon2, $unit) {

	$theta = $lon1 - $lon2;
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	$dist = acos($dist);
	$dist = rad2deg($dist);
	$miles = $dist * 60 * 1.1515;
	$unit = strtoupper($unit);

	if ($unit == "K") {
		return ($miles * 1.609344);
	} else if ($unit == "N") {
		return ($miles * 0.8684);
	} else {
		return $miles;
	}
}

echo distance(32.9697, -96.80322, 29.46786, -98.53506, "M") . " Miles<br>";
echo distance(32.9697, -96.80322, 29.46786, -98.53506, "K") . " Kilometers<br>";
echo distance(32.9697, -96.80322, 29.46786, -98.53506, "N") . " Nautical Miles<br>";

?>
