var pbid = 0;
var resizedata = new Object();


$(document).ready(
		function() {      	
        	$('.fe-button').button();
        	$('#download-zip').hide();
        	$('.image-thumbnail-mock').resizable({
    			resize: function(e,u) {
        			$(this).parent().parent().find('input[name=width]').val(u.size.width);
        			$(this).parent().parent().find('input[name=height]').val(u.size.height);
        		}
        	}); 
        	$('#refresh').hide();
        	$('#resize-crop-width').change( function() { $('#cropResize .image-thumbnail-mock').css('width', $(this).val() + 'px'); } );
        	$('#resize-crop-height').change( function() { $('#cropResize .image-thumbnail-mock').css('height', $(this).val() + 'px'); } );
        	$('.tabs').tabs();
        	$('.staging').slideDown();
        	
        	$('.use-this-resize').click( function() { 
        		if (hasResizeDataErrors(this)) {
        			alert(hasResizeDataErrors());
        		} else {
        			useResize(); 
        		}
        	});
});     	




function useResize()
{
	$('#step-1').fadeOut(200, 
	        function() { 
	            $('#step-2').fadeIn(200);
	            initUploader();
	        });
}

function initUploader()
{
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
        onError     : function(e,q,f,err) { /*alert('Uh-oh, your image is over 2MB and cannot be resized :(');*/ },
        queueSizeLimit : 5,
        onAllComplete : function(e,d) 
            { 
        		$('#step-2 .intro p').fadeOut(300); 
        		$('#step-2 hr').fadeOut(300); 
        		if (d.filesUploaded > 0) {
        			$('#upload-wrapper').fadeOut(); 
        			$('#download-zip').fadeIn();
        			if (d.errors) {
        				$('#download-errors').show();
        			}
        			
        			
        			$('#download-zip').attr('href', 'download.php?dir=' + dirname + '/' + resizedata.resizeType + '_' + resizedata.width + 'x' + resizedata.height);
        			$('#download-zip').click(function() { $(this).fadeOut(300, function() { $('#refresh').fadeIn(); $('#refresh').click(function(){ window.location.href = window.location.href; }); }); return true; });        			
        		}
        		
        		
        		
            }
        });
}

function hasResizeDataErrors(buttonElement)
{
	var error = false;
	resizedata.resizeType = $(buttonElement).parent().attr('id');
	resizedata.width = parseInt($(buttonElement).parent().find('input[name="width"]').val());
	resizedata.width = (isNaN(resizedata.width)) ? 0 : resizedata.width ;
	resizedata.height = parseInt($(buttonElement).parent().find('input[name="height"]').val());
	resizedata.height = (isNaN(resizedata.height)) ? 0 : resizedata.height ;	
	resizedata.dir = 'uploads' + '/' + dirname;
	
	if (0 == resizedata.width || 0 == resizedata.height) {
		error = "You have to enter both width and height!\nOnly numbers are accepted";
	}
	
	return error;
}
