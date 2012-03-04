<?php
require_once 'SLR' . DIRECTORY_SEPARATOR . 'AutoLoader.php';
$slrAutoloader = new SLR_AutoLoader(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'SLR');
$slrAutoloader->initialize();
unset($slrAutoloader);