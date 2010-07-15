<?php
/**
 * Image manipulation class
 * Uses GD
 * 
 * @author Saša Tomislav Mataić <sasa.tomislav [ AT ] mataic.com>
 */
class Nya_Image
{
    
	/**
	 * If image was modified, this attribute holds 
	 * the resulting image resource to be saved
	 * @var resource
	 */
    public $_image = null;
    
    /**
     * Where applicable used for quality setting
     * Currently not implemented 
     * @var int
     */
    private $_quality = 100;
    
    /**
     * "High quality" flag
     * TRUE: imagecopyresampled is used (slower, higher quality)
     * FALSE: imagecopyresized is used (faster, lower quality)
     *  
     * @var bool
     */
    private $_hq = true; 
    
    /**
     * Filesystem directory where image file resides
     * @var string
     */
    private $_path = null;
    
    /**
     * Image file name
     * @var string
     */
    private $_name = null;
    
    /**
     * Full file system path of image file
     * @var string
     */
    private $_filePath = null;
    
    /**
     * Used when image is saved by different name
     * @var string
     */
    private $_originalName = null;
    
    /**
     * Result of getimagesize();
     *
     * @var array
     */
    private $_size = array();
    
    /**
     * Image aspect ratio
     * @var float
     */
    private $_aspectRatio = null;
    
    /**
     * Revert cache
     */
    private $_revert    = array();
    
    /**
     * Image types - used for determining functions for saving
     * and modifying images of different types
     * 
     * @var array
     */
    protected $_imageTypes = array(  
                                    'image/pjpeg'   => array(
                                                                'extension'         => 'jpg',
                                                                'createFunction'    => 'imagecreatefromjpeg',
                                                                'saveFunction'      => 'imagejpeg'
                                                                ),
                                    'image/jpeg'    => array(
                                                                'extension'         => 'jpg',
                                                                'createFunction'    => 'imagecreatefromjpeg',
                                                                'saveFunction'      => 'imagejpeg'
                                                                ),
                                    'image/gif'     => array(
                                                                'extension'         => 'gif',
                                                                'createFunction'    => 'imagecreatefromgif',
                                                                'saveFunction'      => 'imagegif'
                                                                ),
                                    'image/x-png'   => array(
                                                                'extension'         => 'png',
                                                                'createFunction'    => 'imagecreatefrompng',
                                                                'saveFunction'      => 'imagepng'
                                                                ),
                                    'image/png'     => array(
                                                                'extension'         => 'png',
                                                                'createFunction'    => 'imagecreatefrompng',
                                                                'saveFunction'      => 'imagepng'
                                                                ),
                                );
                               
                                 
    /**
     * Create image object, extract image data - 
     * dimensions, 
     * mime type, 
     * aspect ratio
     *
     * @param string $filePath
     */
    public function __construct ($filePath = null, $originalName = null)
    {
        
        if (is_array($filePath)) {
            if (0 != $filePath['error']) {
            	// TODO: translate
                throw new Exception('Invalid image uploaded');
            }
            
            $originalName = $filePath['name'];
            $filePath = $filePath['tmp_name'];
        }
    
        if (null !== $filePath && is_file($filePath)) {
            $this->_size = $this->_revert['size'] = getimagesize($filePath);
            if (!$this->valid())     
            {
                // TODO: translate
                throw new Exception('File "' . $filePath . '" is not of valid image type');
            }
            
            $this->_originalName = $this->_revert['originalName'] = $originalName;
            $this->_filePath = $this->_revert['filePath'] = $filePath;
            $this->_path = $this->_revert['path'] = dirname($filePath);
            $this->_name = $this->_revert['name']  = substr($filePath, strlen($this->_path) + 1); 
            $this->_aspectRatio = $this->_revert['aspectRatio'] = $this->_size[0] / $this->_size[1]; 
        } else {
        	// TODO: translate
            throw new Exception('File "' . $filePath . '" is not of valid image type');
        }
    }
   
    
    /**
     * Revert image to original without the need to reload file
     * @return Nya_Image provides fluent interface
     */
    public function revert()
    {
        $this->_image = null;
        $this->_size = $this->_revert['size'];
        $this->_originalName = $this->_revert['originalName'];
        $this->_filePath = $this->_revert['filePath'];
        $this->_path = $this->_revert['path'];
        $this->_name = $this->_revert['name']; 
        $this->_aspectRatio = $this->_revert['aspectRatio'];
        return $this;
    }
    
    /**
     * Checks if loaded image is valid image file
     * @return bool
     */
    private function valid()
    {
        if (isset($this->_imageTypes[$this->_size['mime']])) {
            return true;
        } else {
            return false;
        }
    }

    
    /**
     * Shorthand function for set/getHighQuality
     * @param $hq
     * @return bool use "high quality" for image transformations
     */
    public function hq($hq = null) 
    {
        if (null === $hq) {
            return $this->getHighQuality();
        } else {
            return $this->setHighQuality($hq);
        }
    }
    
    /**
     * $this->_hq attribute setter
     * 
     * @param $highQuality
     * @return Nya_Image
     */
    public function setHighQuality($highQuality = null)
    {
        if (null === $highQuality) {
            return $this;
        }
        
        if (true == $highQuality) {
            $this->_hq = true;
        } else {
            $this->_hq = false;
        }
        
        return $this;
    }
    
    /**
     * $this->_hq attribute getter
     * 
     * @return bool
     */
    public function getHighQuality()
    {
        return $this->_hq;
    }

    /**
     * Resize image with distorting - resize to $newWidth and $newHeight
     * @param $newWidth
     * @param $newHeight
     * @return Nya_Image provides fluent interface
     */
    public function resize($newWidth = null, $newHeight = null) 
    {
        
        if (null === $newWidth or 0 >= $newWidth) {
            // TODO: translate
            throw new Exception('Invalid dimensions given for ' . __METHOD__);
        }
        
        // if only width given, height is the same - make a square image
        $newWidth = (int) $newWidth;
        $newHeight = (null === $newHeight or 0 >= $newHeight) ? $newWidth : (int) $newHeight;
        
        $width      = $this->_size[0];
        $height     = $this->_size[1];
                
        $createImageFunction    = $this->_imageTypes[$this->_size['mime']]['createFunction'];
        $saveImageFunction      = $this->_imageTypes[$this->_size['mime']]['saveFunction'];
        
        $thumb = imagecreatetruecolor($newWidth, $newHeight);
        $source = $createImageFunction($this->_path . DIRECTORY_SEPARATOR . $this->_name);
        
        $resizeFunctionName = ($this->_hq) ? 'imagecopyresampled' : 'imagecopyresized';
        
        if (!$resizeFunctionName($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height)) {
        	// TODO: translate
           throw new Exception('Failed resizing image: "' . $filePath . '"');
        } else {
            $this->_image = $thumb;
            $this->_size[0] = $newWidth;
            $this->_size[1] = $newHeight;
            $this->_size[3] = 'width="' . $newWidth . '" height="' . $newHeight . '"';
        }
            
        return $this;
    }
    
    /**
     * Resize image to a percentage of original size
     * @param $perc int percent to resize image
     * @return Nya_Image provides fluent interface
     */
    public function resizePercentage($perc = null) 
    {
        if (null === $perc || !is_numeric($perc)) {
            throw new Exception('Invalid dimensions given for ' . __METHOD__);
        } 
        
        $perc = intval(abs($perc));
        $dimensions = $this->size();
        $nWidth = floor($dimensions[0] / (100/$perc)); 
        
        return $this->resizeProportional($nWidth); 
    }
    
    /**
     * Resize to certain size, preserving the aspect ratio
     * @param $newWidth string [optional] width to target
     * @param $newHeight string [optional] height to target
     * @return Nya_Image
     * @throws Exception if both width and hight are provided, but resulting in not matching aspect ratio
     */
    public function resizeProportional($newWidth = null, $newHeight = null)
    {        
        if (null !== $newWidth && null !== $newHeight) {
            $aspectRatio = $newWidth / $newHeight;
            
            if ($aspectRatio !== $this->_aspectRatio) {
            	// TODO: translate
                throw new Exception('Unable to proportionally resize image, invalid width and height given. Both given - invalid aspect ratio of destination image.');
            }
        }
        
        $createImageFunction    = $this->_imageTypes[$this->_size['mime']]['createFunction'];
        $saveImageFunction      = $this->_imageTypes[$this->_size['mime']]['saveFunction'];
        
        // do we have already changed image?
        if (null !== $this->_image) {
            $source = $this->_image;
        } else {
            $source = $createImageFunction($this->_path . DIRECTORY_SEPARATOR . $this->_name);
        }
        
        // determine new dimensions
        if (null !== $newWidth) {
            $newHeight = (int) ($newWidth / $this->_aspectRatio);
        } else {
            $newWidth = (int) ($newHeight * $this->_aspectRatio);
        }
        
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        
        $resizeFunctionName = ($this->_hq) ? 'imagecopyresampled' : 'imagecopyresized';
        
        if (!$resizeFunctionName($resizedImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $this->_size[0], $this->_size[1])) {
        	// TODO: translate
           throw new Exception('Failed resizing image.');
        } else {
            $this->_image = $resizedImage;
            $this->_size[0] = $newWidth;
            $this->_size[1] = $newHeight;
            $this->_size[3] = 'width="' . $newWidth . '" height="' . $newHeight . '"';
        }
            
        return $this;
    }
    
    /**
     * Shrink image if source width or height exceeds 
     * destination dimensions set by parameters
     *
     * @param int $maxWidth maximum width in pixels
     * @param int $maxHeight maximum height in pixels
     * @return Nya_Image
     */
    public function resizeTo($maxWidth = null, $maxHeight = null) 
    {
        
        if ((null === $maxWidth or 0 >= $maxWidth) && (null === $maxHeight or 0 >= $maxHeight) ) {
            throw new Exception('Invalid dimensions given for ' . __METHOD__);
        }
                
        if ($this->_size[0] > $maxWidth) {
            $this->resizeProportional($maxWidth);
        }
        
        if ($this->_size[1] > $maxHeight) {
            $this->resizeProportional(null, $maxHeight);
        }
        
        return $this;
    }
    
    /**
     * Alias of $this->resizeTo()
     * @param $width int maximum width in pixels
     * @param $height int maximum height in pixels
     */
    public function shrinkTo($width = null, $height = null)
    {
        return $this->resizeTo($width, $height);
    }

    /**
     * Resize image to given dimensions with $newWidth & $newHeight
     * If destination image has different aspect ratio from source image - crop
     * Cropping is performed if destination image is wider or narrower than source
     * If destination image is wider - full source width is used, and height is cropped from center
     * If destination image is narrower - full source height is used, and width is cropped from center
     * 
     * @param $newWidth
     * @param $newHeight
     * @return Nya_Image
     */
    public function cropResize($newWidth = null, $newHeight = null) 
    {
        if (null === $newWidth or 0 >= $newWidth) {
            throw new Exception('Invalid dimensions given for ' . __METHOD__);
        }
        
        // if only width given, height is the same - square image
        $newWidth               = (int) $newWidth;
        $newHeight              = (null === $newHeight or 0 >= $newHeight) ? $newWidth : (int) $newHeight;
        $createImageFunction    = $this->_imageTypes[$this->_size['mime']]['createFunction'];
        $saveImageFunction      = $this->_imageTypes[$this->_size['mime']]['saveFunction'];
        $thumb                  = imagecreatetruecolor($newWidth, $newHeight);
        $source                 = $createImageFunction($this->_path . DIRECTORY_SEPARATOR . $this->_name);
        $newAspectRatio         = $newWidth / $newHeight;
        
        // calculate size and position of image portion to crop and resize
        if ($newAspectRatio == $this->_aspectRatio) {
            // aspect ratio is the same - use whole image in resizing
            $width  = $this->_size[0];
            $height = $this->_size[1];
            $src_x = 0;
            $src_y = 0;
        } elseif ($newAspectRatio < $this->_aspectRatio) {
            // new image is narrower than original - use whole height, calculate width (newWidth = originalHeight * newAspectRatio)
            $width  = (int) ($this->_size[1] * $newAspectRatio);
            $height = $this->_size[1];
            $src_x = (int) (($this->_size[0] - $width) / 2);
            $src_y = 0;
        } else {
            // new image is wider than original - use whole width, calculate height (newHeight = originalWidth / newAspectRatio)
            $width  = $this->_size[0];
            $height = (int) ($this->_size[0] / $newAspectRatio);
            $src_x = 0;
            $src_y = (int) (($this->_size[1] - $height) / 2);
        }
        
        $resizeFunctionName = ($this->_hq) ? 'imagecopyresampled' : 'imagecopyresized';
        if (!$resizeFunctionName($thumb, $source, 0, 0, $src_x, $src_y, $newWidth, $newHeight, $width, $height)) {
        	// TODO: translate
            throw new Exception('Failed resizing image for: "' . $source . '"');
        } else {
            $this->_image = $thumb;
            $this->_size[0] = $newWidth;
            $this->_size[1] = $newHeight;
            $this->_size[3] = 'width="' . $newWidth . '" height="' . $newHeight . '"';
        }
            
        return $this;
    }
    
    /**
     * Image is cropped from center with no resizing to 
     * destination dimensions given by $width & $height.
     *  
     * @param $width
     * @param $height
     * @return Nya_Image
     */
    public function cropCenter($newWidth, $newHeight)
    {
        if (null === $newWidth or 0 >= $newWidth) {
            throw new Exception('Invalid dimensions given for ' . __METHOD__);
        }
        
        // if only width given, height is the same - square image
        $width               = (int) $newWidth;
        $height              = (null === $newHeight or 0 >= $newHeight) ? $newWidth : (int) $newHeight;
        
    	$oWidth = $this->_size[0];
    	$oHeight = $this->_size[1];
    	
    	$centerY = ceil($oWidth / 2);
    	$centerX = ceil($oHeight / 2);
    	    	
    	$src_x = $centerY - ceil($width / 2);
    	$src_y = $centerX - ceil($height / 2);
    	
    	$createImageFunction    = $this->_imageTypes[$this->_size['mime']]['createFunction'];
        $saveImageFunction      = $this->_imageTypes[$this->_size['mime']]['saveFunction'];
    	
        $dest = imagecreatetruecolor($width, $height);
        $source = $createImageFunction($this->_path . DIRECTORY_SEPARATOR . $this->_name);
        $resizeFunctionName = ($this->_hq) ? 'imagecopyresampled' : 'imagecopyresized';
        
        if (!$resizeFunctionName($dest, $source, 0, 0, $src_x, $src_y, $width, $height, $width, $height)) {
            throw new Exception('Failed center cropping image for: "' . $source . '"');
        } else {
            $this->_image = $dest;
            $this->_size[0] = $width;
            $this->_size[1] = $height;
            $this->_size[3] = 'width="' . $width . '" height="' . $height . '"';
        }
            
        return $this;
    }
    
    /**
     * getimagesize() return values
     * @return array
     */
    public function size()
    {
        return $this->_size;
    }
    
    /**
     * Returns aspect ratio
     */
    public function aspectRatio()
    {
        return $this->_aspectRatio;
    }
    
    /**
     * //TODO: kaj ovo radi?
     * @param $newName
     */
    public function name($newName = null)
    {
        if (null === $newName) {
            if (null === $this->_originalName) {
                return $this->_name;
            } else {
                return $this->_originalName;
            }
        } else {
            $this->_originalName = $newName;
            return $this;
        }
    }
    
    /**
     * //TODO: koji točno path vraća, mogu li promijeniti path?
     */
    public function path()
    {
        return $this->_path;
    }
    
    /**
     * TODO: što ovo radi?
     */
    public function filePath()
    {
        return $this->_filePath;
    }
    
    /**
     * TODO: što ako smo resajzajzali veličinu?
     * Returns image width in pixels
     */
    public function width()
    {
        return $this->_size[0];
    }
    
    /**
     * TODO: što ako smo resajzajzali veličinu?
     * Returns image height in pixels
     */
    public function height()
    {
        return $this->_size[1];
    }
    
    public function crop($x1 = null, $y1 = null, $x2 = null, $y2 = null)
    {
        throw new Exception('Not implemented');
    }
    
    public function save()
    {               
        // if image wasn't modified - don't do anything, otherwise save modified version 
        if (null === $this->_image) {
        } else {
            $saveImageFunction = $this->_imageTypes[$this->_size['mime']]['saveFunction'];
            if (!$saveImageFunction($this->_image, $this->_filePath)) {
            	// TODO: translate
                throw new Exception("Unable to save modified image '$this->_filePath' as '$this->_filePath' in " . __METHOD__);
            }
        }
    }
    
    public function saveAs($path = null, $name = null, $overWrite = false)
    {
        if (null === $path) {
            // save on same path, different name
            $path = $this->_path;    
        } elseif (!is_dir($path)) {
            // TODO: translate
            throw new Exception('Invalid file path for saving image given: ' . $path);
        }
        
        // file name with what we'll save file
        if (null !== $name) {
            $fileName = $name;
        } elseif (null === $this->_originalName) {
            $fileName = $this->_name;
        } else {
            $fileName = $this->_originalName;
        }
        
        //var_dump($fileName); die;
        
        
        $source = $this->_path . DIRECTORY_SEPARATOR . $this->_name;
        
        if (!$overWrite) {
            // check if file exists with same name, append number to end of name
            $i = 1;
            $baseName = $fileName;
            while (file_exists($path . DIRECTORY_SEPARATOR . $fileName)) {
                $fileNameArray = explode('.', $baseName);
                $fileName = $fileNameArray[0] . $i . '.' . $fileNameArray[1];
                $i++; 
            }
        }
        
        $destination = $path . DIRECTORY_SEPARATOR . $fileName; 
        // update name if changed
        $this->name($fileName);
        
        // if image wasn't modified - copy file, otherwise save modified version 
        if (null === $this->_image) {
            if (!copy($source, $destination)) {
                // TODO: translate
                throw new Exception("Unable to copy '$source' to '$destination' in " . __METHOD__);
            }
        } else {
            $saveImageFunction = $this->_imageTypes[$this->_size['mime']]['saveFunction'];
            if (!$saveImageFunction($this->_image, $destination)) {
                // TODO: translate
                throw new Exception("Unable to save modified image '$source' as '$destination' in " . __METHOD__);
            }
        }
        
        // file is in new location - update path
        $this->_path = $path;
        
        return $this;
    }
    
    public function destroy()
    {
        if (null !== $this->_image) {
            imagedestroy($this->_image); 
        }
        
        return $this;
    }
    
    /**
     * @TODO implement file copying of image
     * @param $path
     * @param $name
     */
    public function copyTo($path = null, $name = null) 
    {
        throw new Exception('Not implemented');
    }

    /**
     * @TODO implement file copying of image
     * @param unknown_type $path
     * @param unknown_type $name
     */
    public function moveTo($path = null, $name = null) 
    {
        throw new Exception('Not implemented');
    }
    
    /**
     * @TODO: get binary file for saving into DB
     */
    public function binary()
    {
        // get binary file for saving into DB
        throw new Exception('Not implemented');
    }
    
    /**
     * @TODO: implement get base 64 encoded image 
     */
    public function base64()
    { 
        throw new Exception('Not implemented');
    }
    
    public function info()
    {
        $filePath = (null === $this->_originalName) ? $this->_name : $this->_originalName;
        $filePath = $this->_path . DIRECTORY_SEPARATOR . $filePath;
        
        return array($filePath, $this->_size[3]);
    }
    
    public function extension()
    {
        return $this->_imageTypes[$this->_size['mime']]['extension'];
    }
    
    /**
     * Check memory needed for image manipulation 
     * @param $targetWidth
     * @param $targetHeight
     */
    public function isResizable($targetWidth, $targetHeight) {
    	$memoryLimit = (int) ini_get('memory_limit');
    	$memoryUsage = (int) ceil(memory_get_usage() / 1024 / 1024);
    	
    	$tweak     = 1.8;
    	$memNeededSource =  ( $this->_size[0] * $this->_size[1] * $tweak * $this->_size['channels']) / 1024 / 1024; 
    	$memNeededTarget =  ( $targetWidth * $targetHeight * $tweak * $this->_size['channels']) / 1024 / 1024;
    	$memNeededSum    = ceil($memNeededSource + $memNeededTarget);
    	$memToSet        = $memoryUsage + $memNeededSum;

    	if ($memToSet < $memoryLimit) {
    		return true;
    	} elseif ($memToSet > 128) {
    		return false;
    	} {
    		$iniMem = $memToSet . 'M';
    		ini_set('memory_limit', $iniMem);
    		
    		if (ini_get('memory_limit') == $iniMem) {
    			return true;
    		} 
    		
    		return false;
    	}
    }
    
    public function __get ($name = null)
    {
        if (isset($this->$name)) {
            return $this->$name;
        } else {
            // TODO: translate
            throw new Exception('Class member "' . $name . '" unrecognized in ' . __CLASS__);
        }
    }
    
    public function __set ($name = null, $value = null)
    {
        if ($name === 'path' && null !== $value) {
            $this->_path = $value;
        } else {
            // TODO: translate
            throw new Exception('Cannot set ' . $name . 'Object of type "' . __CLASS__ . '" permits only "path" member to be set!');
        }
    }
}