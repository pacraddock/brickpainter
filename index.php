<?php 
// index.php contains the form for selecting the source image and the colour palette

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

$output .= '
		<section>
			<header><h2><span>Convert a painting into bricks:</span></h2></header>
			<form method="post" action="result.php" id="brickform">
				<ul class="form">';

// Source inputs - currently only supports URLs
$output .= '
					<li>
						<div class="col1">
							<p class="inputtitle"><span><label for="source">Full URL of image:</label></span></p>
						</div>
						<div class="col2">
							<p><input class="data src" type="text" name="source" id="source" /></p><p class="sub"><strong>Note:</strong> use small / medium images with high contrast for best results. Large-sized images take longer to process, and the largest images take up too much memory.</p>
						</div>
					</li>';

// Dimensions
$output .= '
					<li>
						<div class="col1">
							<p class="inputtitle"><span><label for="size">Dimensions of result, in bricks:</label></span></p><p class="sub">(recommendation: multiples of 8)</p>
						</div>
						<div class="col2">
							<p><input class="data" type="text" name="size" id="size" value="32" /><label for="size"> &nbsp;bricks</label> &nbsp; <select name="xy" id="xy"><option value="wide" id="wide">in width</option><option value="high" id="high">in height</option></select></p>
						</div>
					</li>';

// Colours
$output .= '
					<li>
						<div class="col1">
							<p class="inputtitle"><span><label for="colours">Colours:</label></span></p>
						</div>
						<div class="col2">
							<p><select name="colours" id="colours"><option value="standard" id="standard">Colour</option><option value="grey" id="grey">Greyscale</option><option value="black" id="black">Black &amp; white</option></select><br /><em>(greyscale: black, white, medium stone grey and dark stone grey)</em></p>
						</div>
					</li>';

// Thumbnail
$output .= '
					<li>
						<div class="col1">
							<p class="inputtitle"><span><label for="thumb">Thumbnail?</label></span></p>
						</div>
						<div class="col2">
							<p><input type="checkbox" name="thumb" id="thumb" /> &nbsp; <label for="thumb">Generate a thumbnail instead of a full pixellised image</label></p><p class="sub">(i.e. one pixel = one block)</p>
						</div>
					</li>';

$output .= '
				</ul>
				
				<div class="clear" id="submit"><p class="centre"><input type="submit" name="submit" class="submitbutton" value="Brick this painting" /></p></div>
			</form>
		</section>';

$output .= '
		<section class="text">
			<header><h2><span>About</span></h2></header>
			<p>The <em>Brick Painter</em> transforms any image (of a reasonable size) into bricks, for easy reproduction.</p>
			<p>The <em>Brick Painter</em> has a limited colour palette - it only includes the colours that are available through the LEGO.com webstore as of October 2014.</p>
			<p>The end-result does not (yet) take into account limitations of LEGO plate sizes (e.g. "Earth green" is only available from the LEGO.com store in 1x4 plates, not 1x1 or 1x2).</p>
			<p>The <em>Brick Painter</em> and its author(s) are not affiliated in any way with any companies of the LEGO group.</p>
		</section>
		<section class="text">
			<header><h2 id="terms"><span>Terms of use</span></h2></header>
			<p>The use of the <em>Brick Painter</em> website is subject to acceptance of the terms and conditions stated below. If you do not agree with these terms of use, please do not use this website.</p>
			<p>The <em>Brick Painter</em> is provided for private, non-commercial purposes only, and may only be used for images that you have permission to reproduce.</p>
			<p>By submitting an image for conversion:</p>
			<ul>
			<li>you represent and warrant that you have permission to use that image,</li>
			<li>you grant the operator of the <em>Brick Painter</em> and his/her service providers the right to process the image for the purposes of conversion into bricks, and</li>
			<li>you agree to indemnify and hold harmless the operator of the <em>Brick Painter</em> against any damage or loss resulting from any claims, demands or other liability in relation to the processing of the image for the purposes of conversion.</li>
			</ul>
			<p>No rights whatsoever can be derived from the use of this website or of the <em>Brick Painter</em>.</p>
		</section>';

$output .= '
		<div id="footer"><p class="copyright">Brick Painter is licensed under the GNU GPL v3.0 Licence. <a href="https://github.com/pacraddock/brickpainter" title="GitHub page for the Brick Painter">GitHub project for the page</a>.</p>
		</div>
	</div>
</body>
</html>';

echo $output;

?>
