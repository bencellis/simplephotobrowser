<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Benjy's Photos</title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo $wwwhome; ?>/assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?php echo $wwwhome; ?>/assets/simplephotobrowser.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
    <nav class="navbar navbar-fixed-top" role="navigation">
      <div class="container-fluid">
			<a style="float: right" href="/photos/"><img class="img-responsive" src="<?php echo $wwwhome; ?>/assets/images/header_sml.jpg" /></a>
			<ol class="breadcrumb">
				<?php
					for ($i = 0; $i < count($dirs); $i++) {
						$ctext = '';
						if ('/' . $dirs[$i] == $wwwhome) {
							$btext = 'Home';
							$bpath = $wwwhome;
						}else{
							$btext = $dirs[$i];
							$bpath = '/' . implode('/',array_slice($dirs,0,$i+1));
						}
						$bpath .= '/';
						$bpath = urlencode($bpath);
						if ($viewingAlbum && ($i == count($dirs) - 1)) {
							$ctext = '';
							echo "<li class='active'>$btext</li>";
						}else{
							echo "<li><a href='$bpath'>$btext</a></li>";
						}
					}
				?>
			</ol>
      </div>
    </nav>

    <div class="container-fluid">
		<div class="row">&nbsp;</div>
        <div class="row">
            <?php if ($viewingAlbum): ?>
                    <?php
                    	$thumbsperrow = 4;
                    	$divwidth = ceil(12/$thumbsperrow);
                    	$newrow = true;
                    	$ctr = 0;
                    ?>
        <div class='col-md-12'>
        	<?php foreach ($displayfiles as $container): ?>
        		<?php foreach ($container as $thumb): ?>
		        	<?php if ($newrow): ?>
		        		<div class='row'>
		        	<?php endif; ?>
	        			<div class='col-md-<?php echo $divwidth; ?>'>
	        				<a href="<?php echo urlencode($thumb['wwwpath']); ?>">
	        					<img class="img-responsive img-thumbnail center-block" src='<?php echo $thumb['thumbnail']; ?>' alt='<?php echo $thumb['filename']; ?>' />
	        					<br/>
	        				</a>
	        				<p class="text-center"><?php
	        					if ($thumb['filetype'] == 'directory'){
	        						echo '<span class="glyphicon glyphicon-folder-open"></span>&nbsp;&nbsp;';
	        					}
	        					echo $thumb['filename'];
	        				?></p>
	        				<br />
	        			</div>
	        	<?php
	        		$ctr++;
	        		if ($newrow && ($ctr < $thumbsperrow)){
	        			$newrow = false;
	        		}else if ($ctr ==  $thumbsperrow) {
	        			echo '</div><!-- end row -->';
	        			$newrow = true;
	        			$ctr = 0;
	        		}
	        	?>

	        	<?php endforeach;?>
				<?php
					if ($ctr < $thumbsperrow) {
						echo '</div><!-- end row -->';
					}
				?>
        	<?php endforeach; ?>

        </div>

<?php else: ?>
		<?php
			// map my EXIF Data
			$exif = array();
			$gpsinfo = array();
			$reqexif = array(
				'IFD0.Model' => 'Camera Make/Model',
				'EXIF.DateTimeOriginal' => 'Date and Time Taken',
				'EXIF.ExifImageWidth' => 'Original Width',
				'EXIF.ExifImageLength' => 'Original Height',
				'EXIF.UserComment' => 'Comment',
				'GPS.GPSLatitudeRef' => 'Latitute Ref',
				'GPS.GPSLatitude' => 'Latitute',
				'GPS.GPSLongitudeRef' => 'Longitude Ref',
				'GPS.GPSLongitude' => 'Longitude',
				'GPS.GPSPosition' => 'Map Location',
			);

			foreach ($imagefile['EXIF'] as $fld => $val) {
				if (isset($reqexif[$fld])) {
					if (stripos($fld,'GPS.') === 0) {
						$gpsinfo[$fld] = $val;
					}else{
						$exif[$reqexif[$fld]] = $val;
					}
				}
			}

			$GPSLocationStr = '';
			$GPSLocationDecimal = '';
			if (count($gpsinfo)) {
				// fancy referencing stuff - arrrgggghhhh
				$coordinates[] = &$gpsinfo['GPS.GPSLatitude'];
				$coordinates[] = &$gpsinfo['GPS.GPSLongitude'];
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

						if ($i === 0) {
							if ($GPSLocationStr == '') {
								$GPSLocationStr .= $gpsinfo['GPS.GPSLatitudeRef'] . ' ';
							}else{
								$GPSLocationStr .= $gpsinfo['GPS.GPSLongitudeRef'] . ' ';
							}
						}
						$GPSLocationStr .= $coordinate[$i];

						switch ($i) {
						    case 0:
						        $GPSLocationStr .= '&deg; ';
						        break;
						    case 1:
						        $GPSLocationStr .= "' ";
						        $coordinate[$i] = $coordinate[$i]/60;
						        break;
						    case 2:
						        $GPSLocationStr .= '" ';
						        $coordinate[$i] = $coordinate[$i]/3600;
						        break;
						}
					}
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
		?>
        <div class='col-md-8'>
        	<div>
				<img class="img-responsive center-block" src='<?php echo $imagefile['wwwpath']; ?>' alt='<?php echo $imagefile['filename']; ?>' />
			</div>
        </div>
        <div class='col-md-4'>
				<?php
					if (count($imagefile['IPTC'])) {
						foreach ($imagefile['IPTC'] as $fld=>$val) {
							if (!empty($val)) {
								echo "<div><strong>$fld : </strong>$val</div>";
							}
						}
					}
					if (count($exif)) {
						foreach ($exif as $fld=>$val) {
							if (!empty($val)) {
								echo "<div><strong>$fld : </strong>$val</div>";
							}
						}
					}
					if ($GPSLocationStr) {
						echo "<div><strong>Map Location : </strong>	<br/>
						<a href='https://www.google.co.uk/maps/place/$GPSLocationDecimal' target='_blank'>
							$GPSLocationStr
						</a>
						</div>";
					}
				?>
				<hr />
				<div class="text-center">
				<?php if ($imagefile['previous']): ?>
				    <a title="Previous" href="<?php echo urlencode($imagefile['previous']); ?>"><span class="glyphicon glyphicon-arrow-left"></span></a>&nbsp;
				<?php endif;?>
				<?php if ($imagefile['next']): ?>
				    <a title="Next" href="<?php echo urlencode($imagefile['next']); ?>"><span class="glyphicon glyphicon-arrow-right"></span></a>
				<?php endif;?>
				</div>
		</div>

<?php endif; ?>

      </div><!-- row -->

    </div><!-- container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="<?php echo $wwwhome; ?>/assets/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
   <script src="<?php echo $wwwhome; ?>/assets/js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>

