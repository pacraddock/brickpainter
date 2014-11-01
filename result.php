<?php 
// result.php contains the actual image analysis and manipulation

//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);

// errorMessage function, for any errors we want to display to the user:
function errorMessage($string) {
	global $output;
	$errormsg = $output . '
		<section class="text"><p class="error">' . $string . '</p><p class="back"><a href="index.php">Back to Brick Painter form</a></p></section></div></body></html>';
	return $errormsg;
}

// Header:
$output = '<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="x-ua-compatible" content="IE=Edge"/> 
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Brick Painter</title>
	<link rel="stylesheet" href="brick.css" type="text/css" media="screen" />
	<!--[if lt IE 9]>
		<script src="html5shiv.js"></script>
	<![endif]-->
	<!--[if IE 7]>
		<link rel="stylesheet" href="ie7.css" type="text/css" media="screen, projection">
	<![endif]-->
	<!--[if lte IE 6]>
	<link rel="stylesheet" href="http://universal-ie6-css.googlecode.com/files/ie6.1.1.css" media="screen, projection">
	<![endif]-->
</head>
<body>
	<div id="container">
		<header id="top">
			<h1 id="logo"><a href="index.php"><span>Brick Painter</span></a></h1>
		</header>';

// Start with an image and convert it to a palette-based image

// For URL-based images:
if(!empty($_POST['source'])) {
	$source = $_POST['source'];
	// Ensure there is an http:// prefix:
	if(preg_match('|^https?://|', $source)) {
	} else {
		$source = 'http://' . $source;
	}
} else {
	$errormsg = 'No source file selected.';
	$printerror = errorMessage($errormsg);
	echo $printerror;
	return;
}

// Now we want to check that we can access the file:
$source_headers = @get_headers($source);
if(strpos($source_headers[0],"200")) {
} else {
	$errormsg = 'Invalid source file selected.';
	$printerror = errorMessage($errormsg);
	echo $printerror;
	return;
}

// Next, determining the extension:
$imageextension = pathinfo(parse_url($source,PHP_URL_PATH),PATHINFO_EXTENSION);
$lwextension = strtolower($imageextension);
// As BMP is not supported by imagecreatefromstring, we'll refuse BMP images:
if($lwextension == 'bmp') {
	$errormsg = 'BMP images not supported. Please try another image format.';
	$printerror = errorMessage($errormsg);
	echo $printerror;
	return;
}

// No file upload functionality included. This can serve as a basis (was used for locally hosted files for testing purposes):
// For uploaded images:
/*$imagename = $_POST['name'];
$imageextension = $_POST['ext'];
$lwextension = strtolower($imageextension);
*/

// Because of the size of LEGO base plates (32x32 standard), we want to ensure that by default, we only generate a height of less than 32 blocks if the user specifies a lesser size. We'll be using $heightselected for that purpose - see later in the code
$heightselected = 0;

if(!empty($_POST['size'])) {
	if(!empty($_POST['xy'])) {
		if($_POST['xy'] == 'wide') {
			$wblocks = $_POST['size'];
		} else {
			$hblocks = $_POST['size'];
			$heightselected = 1;
		}
	} else {
		$errormsg = 'No choice of width or height selected.';
		$printerror = errorMessage($errormsg);
		echo $printerror;
		return;
	}
} else {
	$errormsg = 'No choice of dimensions selected.';
	$printerror = errorMessage($errormsg);
	echo $printerror;
	return;
}

// Again, no file upload functionality, but this code can be used to process uploaded images:
// For uploaded images:
/*
if(!file_exists($imagename . '.' . $imageextension)) {
	die('Invalid file selected. Please respect the following URL: ?name=NAME_OF_FILE&ext=EXTENSION_OF_FILE&cmwide=WIDTH_IN_CENTIMETRES');
}

if(($lwextension == 'jpeg') || ($lwextension == 'jpg')) {
	$imagebase = imagecreatefromjpeg($imagename . '.' . $lwextension);
} elseif($lwextension == 'png') {
	$imagebase = imagecreatefrompng($imagename . '.' . $lwextension);
} elseif($lwextension == 'gif') {
	$imagebase = imagecreatefromgif($imagename . '.' . $lwextension);
}
*/

// For URL-based images:
// Is the file an image?
$i = getimagesize($source);
if(!is_array($i) || empty($i)) {
	$errormsg = 'Invalid source file selected.';
	$printerror = errorMessage($errormsg);
	echo $printerror;
	return;
}

// If it is an image, we'll convert it into an image for manipulation through PHP:
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $source); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);
// $imagebase = our image
$imagebase = imagecreatefromstring($data);

// Next, we convert the image from true colour to palette for manipulation
imagetruecolortopalette($imagebase, false, 255);

$imagex = imagesx($imagebase);
$imagey = imagesy($imagebase);

// As we are going to display the result, we want to avoid showing a huge image
// So we're limiting the size of the end-result
if($imagex > 700) {
	$imagey = 700 * $imagey / $imagex;
	$imagex = 700;
}
if($imagey > 900) {
	$imagex = 900 * $imagex / $imagey;
	$imagey = 900;
}

// Now to determine the size and number of blocks
if(!empty($hblocks)) {
	$blocksize = $imagey / $hblocks;
	$wblocks = floor($imagex / $blocksize);
} else {
	$blocksize = $imagex / $wblocks;
	$hblocks = floor($imagey / $blocksize);
}

// Unless "hblocks" is set, we're going to set the minimum height at 32 blocks, which is the size of a standard "large plate" - see previous comment on $heightselected
if($imagey / ($imagex / $wblocks) < 32) {
	if($heightselected == 1) {
		$blocksize = $imagey / $hblocks;
	}
}

// We need to ensure that the number of blocks is an integer, so we'll have to resize the image itself to fit the number of blocks
// First, we determine the proportion by which to resize
$resizeproportion = floor($blocksize) / $blocksize;
// And the "floored" block size
$blocksize = floor($blocksize);
// And finally the resized dimensions:
$sizex = imagesx($imagebase);
$sizey = imagesy($imagebase);
$blocks_wide = floor($imagex * $resizeproportion / $blocksize);
$blocks_high = floor($imagey * $resizeproportion / $blocksize);
$resizex = $blocks_wide*$blocksize;
$resizey = $blocks_high*$blocksize;
// Then we create a $im image that fits these dimensions
$im = imagecreatetruecolor($resizex, $resizey);
imagecopyresampled($im, $imagebase, 0, 0, 0, 0, $resizex, $resizey, $sizex, $sizey);
imagetruecolortopalette($im, false, 255);

$makethumbnail = 0;
// Thumbnail functionality:
if(!empty($_POST['thumb'])) {
	$makethumbnail = 1;
}

// While the $makethumbnail relates to the end-result we'll show, we'll still generate a thumbnail in all cases, as it allows us to ensure we're working by pixel without any problems of resizing & colour approximations later on:
$thumb = imagecreatetruecolor($blocks_wide, $blocks_high);
imagecopyresampled($thumb, $im, 0, 0, 0, 0, $blocks_wide, $blocks_high, $resizex, $resizey);
imagetruecolortopalette($thumb, false, 255);

// Remnant of previous version where we didn't work on the thumbnail at first - there was a need to pixellise the image through applying the $blocksize:
//imagefilter($thumb, IMG_FILTER_PIXELATE, $blocksize, true);
// But with the thumbnail, no longer necessary, as we'd in effect be doing this:
//imagefilter($thumb, IMG_FILTER_PIXELATE, 1, true);


// And now we define the colour arrays:

// First, the colours in RGB arrays:
// (commented lines are colours that are available on the LEGO.com store but not as plates - bricks do exist for some, though
$colorsbw = array(
	array(242, 243, 242),
	array(27, 42, 52),
);

$colorsgrey = array(
	array(242, 243, 242),
	array(27, 42, 52),
	array(163, 162, 164),
	array(99, 95, 97)
);

$colorsstd = array(
	array(242, 243, 242),
	array(215, 197, 153),
//	array(204, 142, 104),
	array(196, 40, 27),
	array(13, 105, 171),
	array(245, 205, 47),
	array(27, 42, 52),
	array(40, 127, 70),
//	array(75, 151, 74),
//	array(160, 95, 52),
	array(110, 153, 201),
	array(218, 133, 64),
	array(164, 189, 70),
//	array(146, 57, 120),
//	array(116, 134, 156),
	array(149, 138, 115),
	array(32, 58, 86),
	array(39, 70, 44),
//	array(120, 144, 129),
	array(123, 46, 47),
//	array(232, 171, 45),
	array(105, 64, 39),
	array(163, 162, 164),
	array(99, 95, 97),
//	array(229, 228, 222),
	array(159, 195, 233),
	array(205, 98, 152),
	array(228, 173, 200),
//	array(253, 234, 140),
	array(159, 112, 183),
	array(85, 106, 50)
);

// And now the colours by name:
$colorsbwname = array(
	'White',
	'Black'
);

$colorsgreyname = array(
	'White',
	'Black',
	'Medium stone grey',
	'Dark stone grey'
);

$colorsstdname = array(
	'White',
	'Brick yellow',
//	'Nougat',
	'Bright red',
	'Bright blue',
	'Bright yellow',
	'Black',
	'Dark green',
//	'Bright green',
//	'Dark orange',
	'Medium blue',
	'Bright orange',
	'Bright yellowish green',
//	'Bright reddish violet',
//	'Sand blue',
	'Sand yellow',
	'Earth blue',
	'Earth green',
//	'Sand green',
	'Dark red',
//	'Flame yellowish orange',
	'Reddish brown',
	'Medium stone grey',
	'Dark stone grey',
//	'Light stone grey',
	'Light royal blue',
	'Bright purple',
	'Light purple',
//	'Cool yellow',
	'Medium lavender',
	'Olive green'
);

// By default, our active colour palette will be the standard, "colours" one:
$colors = $colorsstd;
$colorsname = $colorsstdname;
if(!empty($_POST['colours'])) {
	if($_POST['colours'] == 'grey') {
		$colors = $colorsgrey;
		$colorsname = $colorsgreyname;
	} elseif($_POST['colours'] == 'black') {
		$colors = $colorsbw;
		$colorsname = $colorsbwname;
	} else {
		$colors = $colorsstd;
		$colorsname = $colorsstdname;
	}
}

// Now we want to determine for each colour in the thumbnail which is the closest colour from our palette
$i = 0;
// So we determine the number of colours in our thumbnail
$palletsize = imagecolorstotal($thumb);
// And for each colour:
while($i < $palletsize) {
	$colour = imagecolorsforindex($thumb, $i);
	$closest = 0;
	$lowestdiff = 765;
	foreach($colors as $id => $rgb) {
		// We look at the difference in mathematical terms between the RGB values of our colour and the colour from the palette
		$reddiff = $colour['red'] - $rgb[0];
		$greendiff = $colour['green'] - $rgb[1];
		$bluediff = $colour['blue'] - $rgb[2];
		$diff = abs($reddiff) + abs($greendiff) + abs($bluediff);
		if($diff < $lowestdiff) {
			$lowestdiff = $diff;
			$closest = $id;
		}
	}
	
	// Now that we've gone through all the colours of the palette and determined the one that is closest (i.e. with the smallest $diff) to each of the colours in the actual thumbnail image, we can replace it with that "closest" colour:
	imagecolorset($thumb,$i,$colors[$closest][0],$colors[$closest][1],$colors[$closest][2]);
	
	$i++;
}

// The user needs to know which colours are used and in which quantities, so we have functions on how to find the number of pixels:
function getPixelCountByColor($image, $colour){ 
	$currentCount = 0; 
	for($x = 0; $x < imagesx($image); $x++){ 
		for($y = 0; $y < imagesy($image); $y++){
			$rgb = imagecolorat($image, $x, $y);
			$basecolour = imagecolorsforindex($image, $rgb); 
			if((($basecolour['red'] == $colour[0]) && ($basecolour['blue'] == $colour[2])) && ($basecolour['green'] == $colour[1])) {
				$currentCount++; 
			} 
		} 
	}
	return $currentCount;
}
// And on converting our RGB values to HEX values:
function rgb2hex($rgb) {
	return '#' . sprintf('%02x', $rgb[0]) . sprintf('%02x', $rgb[1]) . sprintf('%02x', $rgb[2]);
}
// And then for each colour from the palette, we show how often it is used (if at all)
$listofcolours = '';
foreach($colors as $id => $rgb_i) {
	$countcol = getPixelCountByColor($thumb,$rgb_i);
	if($countcol > 0) {
		$listofcolours .= '<tr><td>' . $colorsname[$id] . ':</td><td>' . $countcol . '</td><td><span class="colour" style="background-color:' . rgb2hex($rgb_i) . '">&nbsp;</span></td></tr>';
	}
}


// If we're saving to disk:
/*if($makethumbnail == 1) {
	$imgname = 'result/' . $imagename . '-thumb.png';
	imagepng($thumb, $imgname ); // save image as png
} else {
	imagedestroy($im);
	$im = imagecreatetruecolor($resizex, $resizey);
	imagecopyresampled($im, $thumb, 0, 0, 0, 0, $resizex, $resizey, $blocks_wide, $blocks_high);
	imagetruecolortopalette($im, false, 255);
	$imgname = 'result/' . $imagename . '.png';
	imagepng($im, $imgname ); // save image as png
}*/

// If we're simply displaying the image, we need to use ob_start:

// Begin capturing the byte stream
ob_start();

// Generate the byte stream - the image in this case
if($makethumbnail == 1) {
	imagepng($thumb);
} else {
	imagedestroy($im);
	$im = imagecreatetruecolor($resizex, $resizey);
	imagecopyresampled($im, $thumb, 0, 0, 0, 0, $resizex, $resizey, $blocks_wide, $blocks_high);
	imagetruecolortopalette($im, false, 255);
	imagepng($im);
}
$imagevariable = ob_get_contents();

// And finally retrieve the byte stream
$rawImageBytes = ob_get_clean();

// We have finished generating the image in $rawImageBytes for our display on the page, so we can start showing the results:

echo $output;

echo '
		<section class="text">
			<header><h2><span>Your Brick Painting</span></h2></header>
			<p>Here is your Brick Painting:</p>
		';

echo "<p><img alt='Brick Painter result' src='data:image/jpeg;base64," . base64_encode( $rawImageBytes ) . "' /></p>";

echo '<p class="centre large"><a href="index.php">Return to <em>Brick Painter</em> form</a></p>';

// Cleaning up:
imagedestroy($im);
imagedestroy($thumb);

// If we're dealing with a thumbnail, no need to say how big the blocks are - however, if not, we need to specify the size of blocks through our variable $blocksizetext:
if($makethumbnail == 1) {
	$blocksizetext = '';
} else {
	$blocksizetext = ' (blocks are squares of ' . $blocksize . ' pixels wide in the image)';
}

// Additional information on the size of the painting, and list of colours used ($listofcolours from earlier):
echo '<p>Your Brick Painting measures ' . $blocks_wide . ' blocks across and ' . $blocks_high . ' blocks high' . $blocksizetext . '.<br />&nbsp;<br />The following colours are needed:</p><table id="resultlist"><tr><th class="one">Name of colour:</th><th class="two">Number of bricks:</th><th class="three">Colour in picture:</th></tr>' . $listofcolours . '</table>';

// And the footer
echo '
		</section>
		<div id="footer"><p class="copyright">Brick Painter is licensed under the GNU GPL v3.0 Licence. <a href="https://github.com/pacraddock/brickpainter" title="GitHub page for the Brick Painter">GitHub project for the page</a>.</p>
		</div>
	</div>
</body>
</html>';

?>
