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
        <div class="navbar-header">
          <a class="navbar-brand" href="<?php echo $wwwhome; ?>">
            <img class="img-responsive" alt="Header Image" src="<?php echo $wwwhome; ?>/assets/images/header_sml.jpg" />
          </a>
        </div>
         <div class="nav">
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
						$bpath = uri_encode($bpath);
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
		        		<div class='row'> <!-- start new row -->
		        	<?php endif; ?>
	        			<div class='text-center  center-block col-md-<?php echo $divwidth; ?>'>
	        				<a href="<?php echo uri_encode($thumb['wwwpath']); ?>" class="thumbnail">
	        					<img class="img-responsive img-thumbnail" src='<?php echo uri_encode($thumb['thumbnail']); ?>' alt='<?php echo $thumb['filename']; ?>' />
	        					<?php
	        					if ($thumb['filetype'] == 'directory'){
	        						echo '<span class="glyphicon glyphicon-folder-open"></span>&nbsp;&nbsp;';
	        					}
	        					echo $thumb['filename'];
	        					?>
	        				</a>
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
					//if ($ctr < $thumbsperrow) {
						//echo '</div><!-- end row 2 -->';
					//}
				?>
        	<?php endforeach; ?>

        </div>
<?php else: ?>
        <div class='col-md-8'>
            <img class="img-responsive center-block" src='<?php echo $imagefile['wwwpath']; ?>' alt='<?php echo $imagefile['filename']; ?>' />
        </div>
        <div class='col-md-4'>
            <br />
			<?php
				if (count($imagefile['IPTC'])) {
					foreach ($imagefile['IPTC'] as $fld=>$val) {
						if (!empty($val)) {
							echo "<div><strong>$fld : </strong>$val</div>";
						}
					}
				}
				if (count(($exif = $imagefile['EXIF']))) {
					foreach ($exif as $fld=>$val) {
					    if ($fld != 'GPS.LocationDetails') {
							if (!empty($val)) {
								echo "<div><strong>$fld : </strong>$val</div>";
							}
					    }else{
					        $GPSLocationDecimal = $exif[$fld]['decimal'];
					        $GPSLocationStr = $exif[$fld]['text'];
					        echo "<div><strong>Map Location : </strong>	<br/>
					        <a href='https://www.google.co.uk/maps/place/$GPSLocationDecimal' target='_blank'>
					        $GPSLocationStr
					        </a>
					        </div>";
					    }
					}
				}
			?>
			<hr />
			<div class="text-center">
			<?php if ($imagefile['previous']): ?>
			    <a title="Previous" href="<?php echo uri_encode($imagefile['previous']); ?>"><span class="glyphicon glyphicon-arrow-left"></span></a>&nbsp;
			<?php endif;?>
			<?php if ($imagefile['next']): ?>
			    <a title="Next" href="<?php echo uri_encode($imagefile['next']); ?>"><span class="glyphicon glyphicon-arrow-right"></span></a>
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

