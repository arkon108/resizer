<?php

/**
 * Uploadify server side script
 * Upload file and resize it
 */

include_once 'lib/Nya/Image.php';

//$bs = fopen('log', 'a+');
//fwrite($bs, print_r($_POST, true));
$variationDir = $_POST['resizeType'] . '_' . $_POST['width'] . 'x' . $_POST['height'];
//fwrite($bs, 'variation dir:' . $variationDir . "\n");
if (!empty($_FILES)) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$targetPath = str_replace('//','/',$_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/');
	$variationPath = str_replace('//','/', $targetPath . '/' . $variationDir);
	$targetFile =  str_replace('//','/',$targetPath) . $_FILES['Filedata']['name'];
//    fwrite($bs, 'variation path:' . $variationPath . "\n");
		
		if (!is_dir($targetPath)) {
//			fwrite($bs, 'No dir, creating ' . $targetPath . "\n");
			mkdir($targetPath, 0777, true);
		}
        if (!is_dir($variationPath)) {
//          fwrite($bs, 'No dir, creating ' . $variationPath . "\n");
            mkdir($variationPath, 0777, true);
        }
		move_uploaded_file($tempFile, $targetFile);
		
		$image = new Nya_Image($targetFile);
		$width = ($_POST['width']) ? $_POST['width'] : null;
		$height = $_POST['height'] ? $_POST['height'] : null;
//		fwrite($bs, 'method ' . $_POST['resizeType'] . ' width: ' . $width . ' height: ' . $height . "\n");
		$image->$_POST['resizeType']($width, $height);
		$image->saveAs($variationPath, null, true);
		unset($image);
		
		echo "1";
}
//fclose($bs);
?>