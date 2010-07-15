<?php

require_once 'lib/Nya/File.php';

$dir = 'uploads/' . $_GET['dir'];



if (!is_dir($dir)) {
	die('error while reading directory :(');
}

Nya_File::zip($dir, $dir . '.zip');
$fname = substr(strrchr($dir . '.zip', '/'), 1);
Nya_File::sendFile($dir . '.zip', $fname, 'application/zip');