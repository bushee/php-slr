<?php
/**
 * Main SLR library entry point.
 * Simply include this file into your project and enjoy using SLR!
 *
 * PHP version 5.2
 *
 * @category SLR
 * @package  Core
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
require_once 'SLR' . DIRECTORY_SEPARATOR . 'AutoLoader.php';
$slrAutoloader = new SLR_AutoLoader(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'SLR');
$slrAutoloader->initialize();
unset($slrAutoloader);