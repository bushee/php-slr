<?php
require_once 'slr.php';

$config = array(
	'start' => 'E',
	'rules' => array(
		'E' => array(
			array(
				array('E', '+', 'E'),
				create_function('$v', 'return $v[0] + $v[2];')
			),
			array(
				array('E', 'x', 'E'),
				create_function('$v', 'return $v[0] * $v[2];')
			),
			array(
				array('(', 'E', ')'),
				create_function('$v', 'return $v[1];')
			),
			array(
				array('id'),
				create_function('$v', 'return $v[0];')
			)
		),
	)
);

$tokens = array(
	new Token('id', 0.1),
	new Token('x'),
	new Token('('),
	new Token('id', 3.2),
	new Token('+'),
	new Token('id', 14),
	new Token(')'),
);

$slr = new SLR($config);
$parser = new Parser($slr);

var_dump($parser->parse($tokens));