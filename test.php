<?php
require_once 'slr.php';

$config = array(
	'start' => 'E',
	'rules' => array(
		'E' => array(
			array(
				array('E', '+', 'E'),
				''
			),
			array(
				array('E', 'x', 'E'),
				''
			),
			array(
				array('(', 'E', ')'),
				''
			),
			array(
				array('id'),
				''
			),
		),
	)
);

$slr = new SLR($config);