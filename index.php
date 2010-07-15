<?php
session_start();
$sid = session_id();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Online Image Resizer</title>

<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/dark-hive/jquery-ui.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/reset.css" type="text/css" media="all" />
<link rel="stylesheet" href="js/jquery.uploadify-v2.1.0/uploadify.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/default.css" type="text/css" media="all" />
</head>
<body id="index" class="home">

<div id="wrapper">

    <div id="header">
        <h1>Resize yo' images</h1>
        <p class="intro ui-widget-content ui-corner-all">
        	This is a demo application for batch resizing of images. The source code is available at <a href="http://github.com/sasatomislav/resizer">http://github.com/sasatomislav/resizer</a>. 
       	</p>
    </div>

    <div id="content">
    
        <div id="step-1" class="content-step">
            <h2>What sizes do you need to get?</h2>
            
            <div class="tabs">
	            <ul>
	            	<li><a href="#resize">Resize to fixed dimensions</a></li>
	            	<li><a href="#resizeProportional">Resize to maximum</a></li>
	            	<li><a href="#cropResize">Resize and crop from center</a></li>
	            </ul>
	            <div id="resize">
	            	<p>Define to which width and height the images will be resized.</p>
	            	<p>Note that images will be distorted if aspect ratio of entered dimensions is different than on original image.</p>
	            	<hr class="ui-state-disabled" />
	            	<label for="resize-width">Width <input type="text" class="input-text-short" name="width" id="resize-width" /> px</label>
	            	&#215;
	            	<label for="resize-height">Height <input type="text" class="input-text-short" name="height" id="resize-height" /> px</label>
	            	<hr class="ui-state-disabled" />
	            	<button class="use-this-resize fe-button">Ok</button>
	            </div>
	            <div id="resizeProportional">
	            	<p>Set maximum width or height images should not exceed.</p>
	            	<p>If image dimensions are within these limits, image will not be resized.</p>
	            	<p>Note that only one dimension must be set.</p>
	            	<hr class="ui-state-disabled" />
	            	<label for="resize-proportional-width">Width <input type="text" class="input-text-short" name="width" id="resize-proportional-width" /> px</label>
	            	&#215;
	            	<label for="resize-proportional-height">Height <input type="text" class="input-text-short" name="height" id="resize-proportional-height" /> px</label>
	            	<hr class="ui-state-disabled" />
	            	<button class="use-this-resize fe-button">Ok</button>
	            </div>
	            <div id="cropResize">
	            	<p>Resize images to defined dimensions, and crop from center if needed</p>
	            	<hr class="ui-state-disabled" />
	            	<label for="resize-crop-width">Width <input type="text" class="input-text-short" name="width" id="resize-crop-width" /> px</label>
	            	&#215;
	            	<label for="resize-crop-height">Height <input type="text" class="input-text-short" name="height" id="resize-crop-height" /> px</label>
	            	<hr class="ui-state-disabled" />
	            	<button class="use-this-resize fe-button">Ok</button>
	            </div>
        	</div>
        </div>
        
        <div id="step-2" class="content-step">
            <h2>Upload</h2>
            <div class="ui-widget-content ui-corner-all">
            	<p>Select up to 5 files from your computer to upload. File size is limited to 2 MB</p>
            <hr class="ui-state-disabled" />
            <div id="upload-wrapper">
				<input id="uploadfiles" name="uploadfiles" type="file" />
			</div>
			<button id="resize-now" class="fe-button">Resize uploaded images</button>
            </div>
        </div>
    </div>

</div>
<div id="resize-dialog">
	<div class="waitforit">
		<p>Your images are being resized, please wait...</p>
		<hr class="ui-state-disabled" />
		<div id="resize-progress"></div>
	</div>
	<div class="done">
		<p class="done">Your images are ready for download!</p>
		<hr class="ui-state-disabled" />
		<a href="#" id="download-link">Click here to download</a>
	</div>
</div>
<script type="text/javascript">
	var dirname = '<?php echo md5($sid) ?>';
</script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.uploadify-v2.1.0/swfobject.js"></script>
<script type="text/javascript" src="js/jquery.uploadify-v2.1.0/jquery.uploadify.v2.1.0.min.js"></script>
<script type="text/javascript" src="js/default.js"></script>
</body>
</html>
