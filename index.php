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
        <h1>Thumbnailer</h1>
        <h2 id="tagline" class="ui-state-disabled">resize a bunch of images at once<sup>*</sup></h2>
    </div>

    <div id="content">
    
        <div id="step-1" class="content-step">
            <div class="tabs">
	            <ul class="ui-tabs-nav">
	            	<li><a href="#cropResize">Thumbnail them</a></li>
	            	<li><a href="#resizeTo">Resize to maximum size</a></li>
	            	<li><a href="#resize">Resize</a></li>
	            </ul>
	            <div id="cropResize">
	            	<div class="staging ui-widget-content">
	            		<div class="image-thumbnail-mock ui-widget-header ui-state-error" title="drag my corner to resize me!"></div>
	            	</div>
	            	<label for="resize-crop-width">Width <input type="text" value="133" class="input-text-short" name="width" id="resize-crop-width" /> px</label>
	            	&#215;
	            	<label for="resize-crop-height">Height <input type="text" value="100" class="input-text-short" name="height" id="resize-crop-height" /> px</label>
	            	<hr class="ui-state-disabled" />
	            	<button class="use-this-resize fe-button">Ok</button>
	            </div>
	            <div id="resizeTo">
	            	<p>Set maximum width and height for resized images.</p>
	            	<p>If image is smaller, no resizing will be done. Resizing is done proportionally (keeping the aspect ratio).</p>
	            	<hr class="ui-state-disabled" />
	            	<label for="resize-to-width">Width <input type="text" class="input-text-short" name="width" id="resize-to-width" /> px</label>
	            	&#215;
	            	<label for="resize-to-height">Height <input type="text" class="input-text-short" name="height" id="resize-to-height" /> px</label>
	            	<hr class="ui-state-disabled" />
	            	<button class="use-this-resize fe-button">Ok</button>
	            </div>
	            <div id="resize">
	            	<p>Define to which width and height the images will be resized.</p>
	            	<p>Note that images could be distorted.</p>
	            	<hr class="ui-state-disabled" />
	            	<label for="resize-width">Width <input type="text" class="input-text-short" name="width" id="resize-width" /> px</label>
	            	&#215;
	            	<label for="resize-height">Height <input type="text" class="input-text-short" name="height" id="resize-height" /> px</label>
	            	<hr class="ui-state-disabled" />
	            	<button class="use-this-resize fe-button">Ok</button>
	            </div>
        	</div>
        </div>
        
        <div id="step-2" class="content-step">
            <div class="intro ui-widget-content ui-corner-all">
            	<p>Select up to 5 files from your computer to upload. File size is limited to 2 MB</p>
            <hr class="ui-state-disabled" />
            <div id="upload-wrapper">
				<input id="uploadfiles" name="uploadfiles" type="file" />
			</div>
			<p id="download-errors" class="ui-state-error ui-corner-all"><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert"></span>Some of your images were too big for upload!</p>
			<a href="#" id="download-zip" class="fe-button button-action">Download resized images</a>
            <a href="#" id="refresh" class="fe-button button-action">Resize again!</a>
            </div>
        </div>
        <hr class="ui-state-disabled" />
	<p class="ui-widget-content ui-corner-all">* Actually, you can upload and resize only up to 5 images (each being up to 2MB).</p>
	
	<p class="ui-widget-content ui-corner-all">
        	This is a demo application and a prototype. 
            The source code is available at 
            <a href="http://github.com/sasatomislav/resizer">http://github.com/sasatomislav/resizer</a>. 
	</p>
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
