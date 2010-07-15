var pbid = 0;
var value = 1;
var data = new Object();
$(document).ready(
        function() {
        	
        	$('.fe-button').button();
        	$('#resize-now').hide();
        	$('.tabs').tabs();
        	$('#uploadfiles').uploadify({
        		uploader  	: 'js/jquery.uploadify-v2.1.0/uploadify.swf',
        		script    	: 'uploadify.php',
        		cancelImg 	: 'js/jquery.uploadify-v2.1.0/cancel.png',
        		auto      	: true,
        		folder    	: 'uploads/' + dirname + '/',
        		multi		: true,
        		fileDesc  	: 'Image files',
        		fileExt		: '*.gif;*.jpg;*.jpeg;*.png;*.GIF;*.JPG;*.JPEG;*.PNG',
        		//'fileExt'	: '*.gif;*.jpg;*.jpeg;*.png',
        		sizeLimit 	: 2097152,
        		buttonText	: 'Select',
        		wmode		: 'transparent',
        		queueSizeLimit : 5,
        		onAllComplete : function(e,d) { $('#upload-wrapper').fadeOut(); $('#resize-now').fadeIn(); }
        		});
        	
        	$('.use-this-resize').click(
        			function() { 
        				useResize(this);
        			}
        	);
        	
        	$('#resize-now').click(
        			function()
        			{
        				$.get('batch-resize.php', data, 
        						function(d) 
        						{ 
		        					$('#resize-dialog').dialog({ modal: 'true', title: 'doing computery stuff...' }); 
		        					$('#resize-progress').progressbar();
        							pbid = setInterval("progressbarUpdate()", 100) 
        						});
        			}
        		);
});     	


function useResize(buttonElement)
{
	data.resizeType = $(buttonElement).parent().attr('id');
	data.width = parseInt($(buttonElement).parent().find('input[name="width"]').val());
	data.width = (isNaN(data.width)) ? 0 : data.width ;
	data.height = parseInt($(buttonElement).parent().find('input[name="height"]').val());
	data.height = (isNaN(data.height)) ? 0 : data.height ;
	
	data.dir = 'uploads' + '/' + dirname;
	errors = false;
	errorMsg = '';
	switch (data.resizeType) {
	case 'resizeProportional':
		if (data.width && data.height) {
			errors = true;
			errorMsg = "You have to enter only one dimension!\nOnly numbers are accepted";
		}
		break;
	default:
		if (0 == data.width || 0 == data.height) {
			errors = true;
			errorMsg = "You have to enter both width and height!\nOnly numbers are accepted";
		}
		break;
	}
	
	if (errors) {
		alert(errorMsg);
		return false;
	}
	
	$('#step-1').fadeOut(200, function() { $('#step-2').fadeIn(200); });
	
}

function progressbarUpdate()
{
	$.get('progressbar.php', {dir : 'uploads/' + dirname}, function(d) { 
		
		da = d.split('/');
		perc = (da[0] / da[1]) * 100;
		if (perc < 100) {
			$('#resize-progress').progressbar('value', perc);		
		} else {
			$('#resize-progress').progressbar('value', 100);
			clearInterval(pbid);
			$('.waitforit').hide();
			$('.done').show();
			$('#download-link').attr('href', 'download.php?dir=' + dirname + '/' + data.resizeType + '_' + data.width + 'x' + data.height);
		}
		
	});
}