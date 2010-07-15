<?php

require_once 'lib/Nya/Image.php';
require_once 'lib/Nya/File.php';

$dir = $_GET['dir'];
$files = Nya_File::getFilesByDir($dir, 'jpg,jpeg,gif,png');

$vars = array(  $_GET['resizeType']      => array($_GET['width'], $_GET['height']));

$line = 'php resize.php -f "%f%" -m %m% -w %w% -h %h% -p %p% -c %c%' . "\n";
$strs = array('%f%', '%m%', '%w%', '%h%', '%p%', '%c%');
$cnt = count($files) * count($vars);
touch($dir . '/execute.sh');
chmod($dir . '/execute.sh' , 0777);
$bs = fopen($dir . '/execute.sh', 'w');
fwrite($bs, "#!/bin/bash\n");
$i = 1;
foreach ($files as $c => $f) {
    foreach ($vars as $v => $args) {
        $fname = $dir . '/' . $f;
        switch ($v) {
            case 'resizePercentage':
                $replace = array($fname, $v, 0, 0, $args[0], $i . '/' . $cnt);
                $write = str_replace($strs, $replace, $line);
                break;
            default:
                $height = (isset($args[1])) ? $args[1] : 0;
                $replace = array($fname, $v, $args[0], $height, 0, $i . '/' . $cnt);
                $write = str_replace($strs, $replace, $line);
        }
        $i++;
        fwrite($bs, $write);
    }
}

fclose($bs);
touch($dir . '/count');
chmod($dir . '/count' , 0777);
$countFile = fopen($dir . '/count', 'w');
$cntstr = '0/' . $cnt;
fwrite($countFile, $cntstr);
fclose($countFile);

exec($dir . '/execute.sh > /dev/null 2>&1 &');
