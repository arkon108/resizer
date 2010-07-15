<?php
$dir = $_GET['dir'];
$count = file($dir . '/count');
echo $count[0];
unset($count);
