<?php

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);

// settings
if (file_exists('settings.ini')) {
    $settings = parse_ini_file("settings.ini");
}else{
    die('No settings file has been found');
}

// use the settings to define some variables
// I know, I know but sometimes easier to use variables - makes code easier to read
$photodir = $settings['photodir'];
$thumbsdir = $settings['thumbsdir'];
$filext = $settings['filext'];
$getIPTCinfo = false;
if (isset($settings['getIPTCinfo']) && $settings['getIPTCinfo'] == 1) {
    $getIPTCinfo = true;
}
$getEXIFinfo = false;
if (isset($settings['getEXIFinfo']) && $settings['getEXIFinfo'] == 1) {
    $getEXIFinfo = true;
}
// excluded directories
$exdirs = array_map('trim', explode(',' , $settings['excludedirs']));
$defexdirs = array('.', '..', $thumbsdir);
$excludedirectories = array_merge($defexdirs, $exdirs);
// end settings

$wwwhome = dirname($_SERVER['SCRIPT_NAME']);  //only works because all requests come through this script
$dirwwwpath = $wwwhome . $_SERVER['PATH_INFO'];	// this is the request path for directory links
$path = DIRECTORY_SEPARATOR . $photodir. $_SERVER['PATH_INFO'];  //web path
$dir = urldecode($path);	//disk directory
$imagepath = $wwwhome . $path;	// this is the request path for image links
$imagedir = getcwd() . $dir;	// this is directory where images actually live
$thumbph = $wwwhome . '/assets/placeholderthumb.jpg';
$viewingAlbum = true;		// default browsing directories

// for the breadcrumb later
$dirs = explode('/', $dirwwwpath);
# remove empty array values
array_pop($dirs);
array_shift($dirs);

if (is_dir($imagedir)) {
	$viewingAlbum = true;
	$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
	$currentdirfiles = scandir($imagedir);
	$directories = array();
	$images = array();
	$displayfiles = array();

	foreach ($currentdirfiles as $filename) {
		if (in_array($filename,$excludedirectories)) {
			continue;
		}
		$dirfilename = $imagedir.$filename;

		if (finfo_file($finfo, $dirfilename) == 'directory') {
			// get a random thumb
			$imagefiles = array();
			/* check our startdir first - only if that fails do we do the next bit */
			foreach (glob("$dirfilename/*.*",GLOB_NOSORT) as $ff) {
				if (strpos(finfo_file($finfo, $ff),'image') !== false) {
					$imagefiles[] = $ff;
				}
			}

			if (count($imagefiles) == 0) {
				// we have no thumbnails so need to traverse the tree for images
				$imagefiles = _getDirectoryThumbs($finfo,$dirfilename, $excludedirectories, 200);
			}

			if (count($imagefiles)) {
				$thumbnail = $imagefiles[rand(0,count($imagefiles)-1)]; // get a random image file
				// webpath of $thumbnail
				$thumbnail = getThumbnailDetails($thumbnail);
			}else{
				continue;		// no images here
			}

			// we could have a textfile called album.txt which holds a description for the folder/album
			$directories[] = array(
					'filetype' => 'directory',
					'filename' => $filename,
					'wwwpath' => $dirwwwpath . $filename . DIRECTORY_SEPARATOR,
					'filepath' => $dirfilename . DIRECTORY_SEPARATOR,
					'thumbnail' => $thumbnail,
					'description' => $filename
				);
		}else if (strpos(finfo_file($finfo, $dirfilename),'image') !== false) {
			$imageiptc = getIPTCinfo($dirfilename);
			$imagexif = getExifInfo($dirfilename, false);

			// we need to ensure the existance of the thumbnail
			$thumbnail = getThumbnailDetails($dirfilename);

			$images[] = array(
					'filetype' => 'file',
					'filename' => $filename,
					'wwwpath' => $dirwwwpath . $filename . $filext,		// pretend it is a html file
					'filepath' => $dirfilename,
					'thumbnail' => $thumbnail,
					'IPTC' => $imageiptc,
					'EXIF' => $imagexif,
				);

		} // otherwise we are not interested
	}
	finfo_close($finfo);
	//die;
	$displayfiles['directories'] = $directories;
	$displayfiles['images'] = $images;

// 	echo '<pre>' . print_r($displayfiles,true) . '</pre>';
// 	die;
}else{

    // determine if we have a call to a valid file
    // if not we need to redirect the user to the front page

	// strip pretend file extention
	$filedir = str_replace($filext,'',$imagedir);
	$filepath = str_replace($filext,'',$imagepath);

	if (file_exists($filedir)) {
    	$viewingAlbum = false;
    	$filename = basename($filepath);
    	$imagefile = array(
    		'filetype' => 'file',
    		'filename' => $filename,
    		'wwwpath' => $filepath,
    		'filepath' => $filedir,
    		'thumbnail' => getThumbnailDetails($filedir),
    		'IPTC' => getIPTCinfo($filedir),
    		'EXIF' => getExifInfo($filedir),
    		'imagesize' => getimagesize($filedir)
    	);

    	// get details for the previous and nextfiles
    	$dirfiles = glob(dirname($imagedir) . '/*.jpg');    // only want files
    	$pos = (int) array_search($filedir, $dirfiles);
    	$imagefile['previous'] = $pos == 0 ? '' : dirname($dirwwwpath) . '/' . basename($dirfiles[$pos-1]) . $filext;
    	$imagefile['next'] = $pos == count($dirfiles) - 1 ? '' : dirname($dirwwwpath) . '/' . basename($dirfiles[$pos+1]) . $filext;

	}else{
	    // this file does not exist
	    header('Location: ' . $wwwhome, true, 301);
	    die();         // ensure no further processing
	}
}

include_once('content.php');

function getIPTCinfo($imagefile,$include=null) {
	global $getIPTCinfo;
	// get some info from the IPTC tags in the files
	$imageiptc = array();

	$iptcHeader = array	(
	    '2#005'=>'Document Title',
	    '2#010'=>'Urgency',
	    '2#015'=>'Category',
	    '2#020'=>'Subcategories',
	    '2#025'=>'Keywords',	// array
	    '2#040'=>'Special Instructions',
	    '2#055'=>'Creation Date',
	    '2#080'=>'Author Byline',
	    '2#085'=>'Author Title',
	    '2#090'=>'City',
	    '2#095'=>'State',
	    '2#101'=>'Country',
	    '2#103'=>'OTR',
	    '2#105'=>'Headline',
	    '2#110'=>'Source',
	    '2#115'=>'Photo Source',
	    '2#116'=>'Copyright',
	    '2#120'=>'Caption',
	    '2#122'=>'Caption Writer',
	    '2#100'=>'Country Code',
	);

	if (!isset($include)) {
		$include = $getIPTCinfo;
	}
	if ($include) {
		$size = getimagesize($imagefile, $info);
		if(isset($info['APP13'])) {
			$iptc = iptcparse($info['APP13']);
			foreach($iptc as $key => $value){
				if (isset($iptcHeader[$key])) {
				    if (($value = filter_var_array($value, FILTER_SANITIZE_STRING)) != false) {
				        $imageiptc[$iptcHeader[$key]] = implode(', ',$value);
				    }
				}
			}
		}
	}
	return $imageiptc;
}

function getExifInfo($imagefile,$include=null) {
	global $getEXIFinfo;

	$imagexif = array();
	$gpsinfo = array();

	// map my EXIF Data
	$reqexif = array(
	    'IFD0.Model' => 'Camera Make/Model',
	    'EXIF.DateTimeOriginal' => 'Date and Time Taken',
	    'EXIF.ExifImageWidth' => 'Original Width',
	    'EXIF.ExifImageLength' => 'Original Height',
	    'EXIF.UserComment' => 'Comment',
	    'GPS.GPSLatitudeRef' => 'Latitute Ref',
	    'GPS.GPSLatitude' => 'Latitute',
	    'GPS.GPSLongitudeRef' => 'Longitude Ref',
	    'GPS.GPSLongitude' => 'Longitude'
	);

	if (!isset($include)) {
		$include = $getEXIFinfo;
	}

	if ($include) {
		if ($exif = exif_read_data($imagefile, 0, true)) {
			foreach ($exif as $key => $section) {
				foreach ($section as $name => $val) {
				    if ($key == 'GPS' || ($val = filter_var($val, FILTER_SANITIZE_STRING)) != false) {
				        $fldname = "$key.$name";
				        if (isset($reqexif[$fldname])) {
    				        if ($key == 'GPS') {        // not in exif
    				            if ($val) {
    				                $gpsinfo[$fldname] = $val;
    				            }
    				        }else{
    				            $imagexif[$reqexif[$fldname]] = $val;
    				        }
				        }
				    }
				}
			}
			// do we have any GPS info???
			// this is an extension to create a string GPS Location that we can use with Maps
			if (count($gpsinfo)) {
			    $imagexif["GPS.LocationDetails"] = addEXIFLocationGPS($gpsinfo);
			}
		}
	}

	return $imagexif;
}

// function to extend the EXIF Data with a text version of the GPS Location
// for use in a map link
function addEXIFLocationGPS($gpsinfo) {
    $GPSLocationStr = '';
    $GPSLocationDecimal = '';

    if (count($gpsinfo)) {
        // fancy referencing stuff - arrrgggghhhh
        $coordinates[] = &$gpsinfo['GPS.GPSLatitude'];
        $coordinates[] = &$gpsinfo['GPS.GPSLongitude'];
        $latitudedone = false;
        foreach ($coordinates as &$coordinate) {
            for ($i = 0; $i < 3; $i++) {
                $part = explode('/', $coordinate[$i]);
                if (count($part) == 1) {
                    $coordinate[$i] = $part[0];
                } else if (count($part) == 2) {
                    $coordinate[$i] = floatval($part[0])/floatval($part[1]);
                } else {
                    $coordinate[$i] = 0;
                }

                switch ($i) {
                    case 0:
                        $GPSLocationStr .= $coordinate[$i] . '&deg;';
                        break;
                    case 1:
                        $GPSLocationStr .= $coordinate[$i] . "'";
                        $coordinate[$i] = $coordinate[$i]/60;
                        break;
                    case 2:
                        $GPSLocationStr .= round(floatval($coordinate[$i]),1). '"';
                        $coordinate[$i] = $coordinate[$i]/3600;
                        if (!$latitudedone) {
                            $GPSLocationStr .= $gpsinfo['GPS.GPSLatitudeRef'];
                            $latitudedone = true;
                        }else{
                            $GPSLocationStr .= $gpsinfo['GPS.GPSLongitudeRef'];
                        }
                        break;
                }
            }
            $GPSLocationStr .= ' ';
        }

        // now for decimal values for the map reference
        // latitude
        $multiplier = 0;
        if ($gpsinfo['GPS.GPSLatitudeRef'] == 'S') {
            $multiplier = -1;
        }else if ($gpsinfo['GPS.GPSLatitudeRef'] == 'N') {
            $multiplier = 1;
        }
        $GPSLocationDecimal .= (string) floatval($multiplier * ($gpsinfo['GPS.GPSLatitude'][0] + $gpsinfo['GPS.GPSLatitude'][1] + $gpsinfo['GPS.GPSLatitude'][2])) . ',';

        // longitude
        $multiplier = 0;
        if ($gpsinfo['GPS.GPSLongitudeRef'] == 'W') {
            $multiplier = -1;
        }else if ($gpsinfo['GPS.GPSLongitudeRef'] == 'E') {
            $multiplier = 1;
        }
        $GPSLocationDecimal .= (string) ($multiplier * ($gpsinfo['GPS.GPSLongitude'][0] + $gpsinfo['GPS.GPSLongitude'][1] + $gpsinfo['GPS.GPSLongitude'][2]));
    }

    return array('decimal'=> $GPSLocationDecimal, 'text'=> $GPSLocationStr);
}

// function to return a urlencoded uri without breaking the slashes :(
function uri_encode($url) {
    if ($url) {
        $slashcode = rawurlencode('/');
        $url = rawurlencode($url);
        // put the slashes back
        $url = str_replace($slashcode,'/',$url);
    }
    return $url;
}

// function to return an existing thumbnail file details
function getThumbnailDetails($filepath) {
	global $imagedir, $imagepath, $thumbsdir, $thumbph;
	$thumbnailpath = '';

	$fileinfo = pathinfo($filepath);
	$thumbnailpath = $fileinfo['dirname'] . DIRECTORY_SEPARATOR . $thumbsdir . DIRECTORY_SEPARATOR . $fileinfo['basename'];

	if (!file_exists($thumbnailpath)) {
		$thumbnailpath = $thumbph;
	}

	// turn it into a web path
	$thumbnailpath = str_replace($imagedir,$imagepath,$thumbnailpath);

	return $thumbnailpath;
}

// returns an array of image files from a directory tree
function _getDirectoryThumbs($finfo, $dir, $excludedirs = array(), $maxfilecnt=200, $currentcount=0) {
	$dirHandle = opendir($dir);
	$files = array();
	while ($file = readdir($dirHandle)){
		$dirfilename = $dir . '/' . $file;
		if (finfo_file($finfo, $dirfilename) == 'directory'){
			if (!(in_array($file, $excludedirs))) {
				$files = array_merge($files, _getDirectoryThumbs($finfo, $dirfilename, $excludedirs, $maxfilecnt, $currentcount)); // Correct call and fixed counting
			}
		} else {
			if (strpos(finfo_file($finfo, $dirfilename),'image') !== false) {
				$currentcount++;
				if ($currentcount > $maxfilecnt) {
					break;
				}
				$files[] = $dirfilename;
			}
		}
	}
	return $files;
}

?>
