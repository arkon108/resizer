<?php
/**
 * cli script for resizing a single image
 * using Nya_Image
 * 
 * 
 * options:
 *  -f filename
 *  -m method for resizing
 *  -w width
 *  -h height
 *  -p percentage
 *  -c count 
 */

require_once 'lib/Nya/Image.php';

$args = getopt('f:m:w:h:c:p:');

$dir = explode('/', $args['f']);
array_pop($dir);
$dir = implode('/', $dir);

$img = new Nya_Image($args['f']);

switch ($args['m']) {
    case 'resizePercentage':
        $dirToSave = $dir . '/' . $args['m'] . '_' . $args['p'];
        $img->$args['m']($args['p']);
        break;
    default:
        $height = (isset($args['h']) && !empty($args['h'])) ? $args['h'] : null; 
        $dirToSave = $dir . '/' . $args['m'] . '_' . $args['w'] . 'x' . $args['h'];        
        $img->$args['m']($args['w'], $height);
}

if (!is_dir($dirToSave)) {
	mkdir($dirToSave); 
}
        
$img->saveAs($dirToSave, null, true);
unset($img);

$cnt = fopen($dir . '/count', 'w');
$cntstr = (isset($args['c']) && !empty($args['c'])) ? $args['c'] : '1/1';
fwrite($cnt, $cntstr);
fclose($cnt);
