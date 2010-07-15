<?php
/**
 * General all-purpose filesystem functions
 * Most of the functions are declared statically
 * 
 * @package Nya_File
 * @author Saša Tomislav Mataić <sasa.tomislav [ AT ] mataic.com>
 */

class Nya_File
{
	
    protected static $_mimes = array(
            "pdf" => "application/pdf",
            "txt" => "text/plain",
            "html" => "text/html",
            "htm" => "text/html",
            "exe" => "application/octet-stream",
            "zip" => "application/zip",
            "doc" => "application/msword",
            "xls" => "application/vnd.ms-excel",
            "ppt" => "application/vnd.ms-powerpoint",
            "gif" => "image/gif",
            "png" => "image/png",
            "jpeg"=> "image/jpg",
            "jpg" =>  "image/jpg",
            "php" => "text/plain",
            "csv" => "text/csv"
         ); 	
     
    /**
     * Get array of directories contained in directory 
     *
     * @author stmataic@gmail.com
     * @param string $dir Path to directory to read from
     * @return array List of directories found
     * @throws Exception
     */
    public static function readDirsFromDir($dir = null)
    {
        if (null == $dir) {
            throw new Exception('No directory to read from!');
        }
        
        if (!is_dir($dir)) {
            throw new Exception('Invalid directory to read from! ' . $dir);
        }
        
        if ($handle = opendir($dir)) {
            while (false !== ($fileName = readdir($handle))) {
                if ('.' != $fileName && '..' !=  $fileName && is_dir($dir . DIRECTORY_SEPARATOR . $fileName)) {
                    $dirs[] = $fileName;
                }
            }
            closedir($handle);
        
        }
        return (array) $dirs;
    }
    
    /**
     * alias of self::readFilesFromDir($dir, $extensions)
     *
     * @param string $dir directory to read and return files from
     * @param string $extensions comma delimited filter, what to get - example: 'png,gif,jpg'
     */
    public static function getFilesByDir($dir = null, $extensions = null) {
        return self::readFilesFromDir($dir, $extensions);
    }
    
    
    /**
     * Returns an array of files for a given directory
     * If provided with a list of extensions, will return only files with that extensions
     *
     * @param string $dir directory to read and return files from
     * @param string $extensions comma delimited filter, what to get - example: 'png,gif,jpg'
     * @return array files read from $dir
     * @throws Exception
     */
    public static function readFilesFromDir($dir = null, $extensions = null)
    {
        if (null == $dir) {
            throw new Exception('No directory to read from!');
        }
        
        if (!is_dir($dir)) {
            throw new Exception('Invalid directory to read from! ' . $dir);
        }
        
        $filesArray = array();
        
        if (null === $extensions)
        {
            $extensions = '';
        }
        
        $forbiddenExtensions = '.,..';
        
        if ($handle = opendir($dir)) {
            while (false !== ($fileName = readdir($handle))) {
                $fileExtensionArray = explode('.', $fileName);
                $fileExtension = $fileExtensionArray[count($fileExtensionArray)-1];
                
                if('' == $fileExtension) {
                    continue;
                }
 
                $isValid = stripos($extensions, $fileExtension);
                if (false !== $isValid) {
                    $filesArray[] = $fileName;
                }
            }
            closedir($handle);
        
        }
        return $filesArray;
    }
    
    /**
     * Checks and saves form uploaded files to directory
     *
     * @param array $file HTML form $_FILES superglobal
     * @param string $directory path where to save file
     * @param array $allowedTypes associative array of allowed MIME types
     * @return false | file path
     */
    public function upload($file, $directory, $allowedTypes = null)
    {
        if ($file['error'] == 0) {
            if (null !== $allowedTypes)
            {
                if (!array_key_exists($file['type'], $allowedTypes))
                {
                    return false;
                }
            }

            $newFileName = $directory . DIRECTORY_SEPARATOR . $file['name'];
            if (move_uploaded_file($file['tmp_name'], $newFileName))
            {
                return $file['name'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    /**
     * Delete a directory, if not empty, delete all the contents, then delete dir itself
     *
     * @param string $directory directory path
     * @return bool Succesfull deletion
     * @throws Exception
     */
    public static function deleteDirectory($directory = null)
    {
        if (null === $directory or !is_dir($directory)) {
            throw new Exception("No directory: $directory");
        }
        
        // delete all directory content first
        self::emptyDirectory($directory);
        
        return rmdir($directory);
    }
    
    /**
     * Empty directory of contents recursively
     *
     * @param string $directory Directory filepath
     * @return void
     * @throws Exception
     */
    public static function emptyDirectory($directory = null)
    {
        if (null === $directory or !is_dir($directory)) {
            throw new Exception("No directory: $directory");
        }
       
        if ($handle = opendir($directory)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    if (is_dir($directory . DIRECTORY_SEPARATOR . $file)) {
                        self::emptyDirectory($directory . DIRECTORY_SEPARATOR . $file);
                        self::deleteDirectory($directory . DIRECTORY_SEPARATOR . $file);
                    } else {
                        unlink($directory . DIRECTORY_SEPARATOR . $file);
                    }
                }
            }
        closedir($handle);
        }
    }
    
    
    /**
     * A function to copy files from one directory to another one, including subdirectories and
     * nonexisting or newer files. Function returns number of files copied.
     * This function is PHP implementation of Windows xcopy  A:\dir1\* B:\dir2 /D /E /F /H /R /Y
     * Syntaxis: [$returnstring =] dircopy($sourcedirectory, $destinationdirectory [, $offset] [, $verbose]);
     * Example: $num = dircopy('A:\dir1', 'B:\dir2', 1);
     *    
     * Original by SkyEye.  Remake by AngelKiha.
     * Linux compatibility by marajax. System indiference path compatibility by stmataic.
     * 
     * Offset count added for the possibilty that it somehow miscounts your files.  This is NOT required.
     * 
     * Remake returns an explodable string with comma differentiables, in the order of:
     * Number copied files, Number of files which failed to copy, Total size (in bytes) of the copied files,
     * and the files which fail to copy.  Example: 5,2,150000,\SOMEPATH\SOMEFILE.EXT|\SOMEPATH\SOMEOTHERFILE.EXT
     * If you feel adventurous, or have an error reporting system that can log the failed copy files, they can be
     * exploded using the | differentiable, after exploding the result string.
     *
     * @author SkyEye
     * @param string $srcdir
     * @param string $dstdir
     * @param int $offset
     * @param bool $verbose
     * @return string Explodable string of data: Num copied files, num failed files, total size in B, list of failed files
     */
    public static function copyDirectory($srcdir, $dstdir, $offset = '', $verbose = false)
    {
        
        if(!isset($offset)) $offset=0;
        $num = 0;
        $fail = 0;
        $sizetotal = 0;
        $fifail = '';
        if(!is_dir($dstdir)) mkdir($dstdir);
        if($curdir = opendir($srcdir)) {
            while($file = readdir($curdir)) {
                if($file != '.' && $file != '..') {
                    $srcfile = $srcdir . DIRECTORY_SEPARATOR . $file;
                    $dstfile = $dstdir . DIRECTORY_SEPARATOR . $file;
                    if(is_file($srcfile)) {
                        if(is_file($dstfile)) $ow = filemtime($srcfile) - filemtime($dstfile); else $ow = 1;
                        if($ow > 0) {
                            if($verbose) echo "Copying '$srcfile' to '$dstfile'...<br />";
                            if(copy($srcfile, $dstfile)) {
                                touch($dstfile, filemtime($srcfile)); $num++;
                                chmod($dstfile, 0777);    # added by marajax
                                $sizetotal = ($sizetotal + filesize($dstfile));
                                if($verbose) echo "OK\n";
                            }
                            else {
                                if($verbose) echo "Error: File '$srcfile' could not be copied!<br />\n";
                                $fail++;
                                $fifail = $fifail.$srcfile.'|';
                            }
                        }
                    }
                    else if(is_dir($srcfile)) {
                        $res = explode(',',$ret);
                        $ret = self::copyDirectory($srcfile, $dstfile, '', $verbose);
                        $mod = explode(',',$ret);
                        $imp = array($res[0] + $mod[0],$mod[1] + $res[1],$mod[2] + $res[2],$mod[3].$res[3]);
                        $ret = implode(',',$imp);
                    }
                }
            }
            closedir($curdir);
        }
        $red = explode(',',$ret);
        $ret = ($num + $red[0]).','.(($fail-$offset) + $red[1]).','.($sizetotal + $red[2]).','.$fifail.$red[3];
        return $ret;
    }
    


    /**
     * Zip directory
     * @author http://stackoverflow.com/users/89771/alix-axel
     * @param string $source directory
     * @param string $destination file name
     */
	public function zip($source, $destination)
	{
	    if (extension_loaded('zip') === true) {
	        if (file_exists($source) === true) {
                $zip = new ZipArchive();
	
                if ($zip->open($destination, ZIPARCHIVE::CREATE) === true) {
    				$source = realpath($source);
	
	                if (is_dir($source) === true) {
	                	$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
	
	                    foreach ($files as $file) {
	                    	$file = realpath($file);
	
	                        if (is_dir($file) === true) {
								$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
							} else if (is_file($file) === true) {
								$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
	                        }
	                     }
	                } else if (is_file($source) === true) {
						$zip->addFromString(basename($source), file_get_contents($source));
					}
	            }
				return $zip->close();
	        }
	    }
	    return false;
	}
    
    
    /**
     * Non-recursive method to get class names 
     * for PHP source files in a directory where files are named by classes
     * @param $dir
     * @param $prefix
     * @return array
     */
    public static function getClassNamesByDir($dir, $prefix = '')
    {
    	$classFilenames = self::getFilesByDir($dir, 'php');
    	$return = array();
    	
    	foreach ($classFilenames as $fn) {
    		$return[] = $prefix . substr($fn, 0, strlen($fn) - 4);
    	}
    	
    	
    	return $return;
    }
    
	/*
     This function takes a path to a file to output ($file), 
     the filename that the browser will see ($name) and 
     the MIME type of the file ($mime_type, optional).
     
     If you want to do something on download abort/finish,
     register_shutdown_function('function_name');
     */
    public static function sendFile($file, $name, $mime_type='')
    {
        if(!is_readable($file)) {
            throw new Exception('File not found or inaccessible!');
        } 
         
        $size = filesize($file);
        $name = rawurldecode($name);
         
        if($mime_type == ''){
            $file_extension = strtolower(substr(strrchr($file,"."),1));
            if(array_key_exists($file_extension, self::$_mimes)){
                $mime_type = self::$_mimes[$file_extension];
             } else {
                $mime_type = "application/force-download";
             }
         }
                 
         // required for IE, otherwise Content-Disposition may be ignored
        if(ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }
         
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="'.$name.'"');
        header("Content-Transfer-Encoding: binary");
        header('Accept-Ranges: bytes');
         
        /* The three lines below basically make the 
           download non-cacheable */
        header("Cache-control: private");
        header('Pragma: private');
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
         
        // multipart-download and download resuming support
        if(isset($_SERVER['HTTP_RANGE']))
        {
           list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
           list($range) = explode(",",$range,2);
           list($range, $range_end) = explode("-", $range);
           $range=intval($range);
           if(!$range_end) {
               $range_end=$size-1;
           } else {
               $range_end=intval($range_end);
           }
         
           $new_length = $range_end-$range+1;
           header("HTTP/1.1 206 Partial Content");
           header("Content-Length: $new_length");
           header("Content-Range: bytes $range-$range_end/$size");
        } else {
           $new_length=$size;
           header("Content-Length: ".$size);
        }
         
        /* output the file itself */
        $chunksize = 1*(1024*1024); //you may want to change this
        $bytes_send = 0;
        if ($file = fopen($file, 'r'))
        {
           if(isset($_SERVER['HTTP_RANGE']))
           fseek($file, $range);
        
           while(!feof($file) && 
               (!connection_aborted()) && 
               ($bytes_send<$new_length)
                 )
           {
               $buffer = fread($file, $chunksize);
               print($buffer); //echo($buffer); // is also possible
               flush();
               $bytes_send += strlen($buffer);
           }
        fclose($file);
        } else die('Error - can not open file.');
         
        die();
    }                
}