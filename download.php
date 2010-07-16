<?php

/**
 * Package resized images in a zip file, place it in the Downloads dir
 * Delete all uploaded files and variations 
 * Send zip to the user
 */

require_once 'lib/Nya/File.php';

$dir = 'uploads/' . $_GET['dir'];
$baseDir = explode('/', $dir);
array_pop($baseDir);
$baseDir = implode('/', $baseDir);

if (!is_dir($dir)) {
	die('error while reading directory :(');
}

Nya_File::zip($dir, $dir . '.zip');
$fname = substr(strrchr($dir . '.zip', '/'), 1);
$dlName = md5($dir);
$dlFile = 'downloads/' . $dlName;
rename($dir . '.zip', $dlFile);
Nya_File::emptyDirectory($baseDir);

Nya_File::sendFile($dlFile, $fname, 'application/zip');

