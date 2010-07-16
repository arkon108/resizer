var pbid = 0;
var value = 1;
var resizedata = new Object();
$(document).ready(
        function() {
        	
        	$('.fe-button').button();
        	$('#download-zip').hide();
        	$('#refresh').hide();
        	$('.tabs').tabs();
        	
        	$('.use-this-resize').click(
        			function() { 
        				useResize(this);
        			}
        	);
});     	


function useResize(buttonElement)
{
	resizedata.resizeType = $(buttonElement).parent().attr('id');
	resizedata.width = parseInt($(buttonElement).parent().find('input[name="width"]').val());
	resizedata.width = (isNaN(resizedata.width)) ? 0 : resizedata.width ;
	resizedata.height = parseInt($(buttonElement).parent().find('input[name="height"]').val());
	resizedata.height = (isNaN(resizedata.height)) ? 0 : resizedata.height ;
	
	resizedata.dir = 'uploads' + '/' + dirname;
	errors = false;
	errorMsg = '';
	switch (resizedata.resizeType) {
	case 'resizeProportional':
		if (resizedata.width && resizedata.height) {
			errors = true;
			errorMsg = "You have to enter only one dimension!\nOnly numbers are accepted";
		}
		break;
	default:
		if (0 == resizedata.width || 0 == resizedata.height) {
			errors = true;
			errorMsg = "You have to enter both width and height!\nOnly numbers are accepted";
		}
		break;
	}
	
	if (errors) {
		alert(errorMsg);
		return false;
	}
	
	$('#step-1').fadeOut(200, 
	        function() { 
	            $('#step-2').fadeIn(200);
	            
	            $('#uploadfiles').uploadify({
	                uploader    : 'js/jquery.uploadify-v2.1.0/uploadify.swf',
	                script      : 'uploadify.php',
	                cancelImg   : 'js/jquery.uploadify-v2.1.0/cancel.png',
	                auto        : true,
	                folder      : 'uploads/' + dirname + '/',
	                multi       : true,
	                fileDesc    : 'Image files',
	                fileExt     : '*.gif;*.jpg;*.jpeg;*.png;*.GIF;*.JPG;*.JPEG;*.PNG',
	                sizeLimit   : 2097152,
	                buttonText  : 'Select',
	                wmode       : 'transparent',
	                scriptData  : resizedata,
	                queueSizeLimit : 5,
	                onAllComplete : function(e,d) 
	                    { 
	                        $('#upload-wrapper').fadeOut(); 
	                        $('#download-zip').fadeIn();
	                        $('#download-zip').attr('href', 'download.php?dir=' + dirname + '/' + resizedata.resizeType + '_' + resizedata.width + 'x' + resizedata.height);
	                        $('#download-zip').click(function() { $(this).fadeOut(300, function() { $('#refresh').fadeIn(); $('#refresh').click(function(){ window.location.href = window.location.href; }) }); return true; });
	                        
                        }
	                });
	        });
	
}
