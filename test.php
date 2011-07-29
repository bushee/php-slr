<?php
require_once 'slr.php';

$parserConfig = array(
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
	),
	'success' => create_function('$v', 'echo "$v\n"; return $v;')
);

$lexerConfig = array(
	'initial' => array(
		array('string', '+', create_function('&$value', 'return \'+\';')),
		array('string', '*', create_function('&$value', 'return \'x\';')),
		array('string', '(', create_function('&$value', 'return \'(\';')),
		array('string', ')', create_function('&$value', 'return \')\';')),
		array('regex', '/[0-9]+/', create_function('&$value', 'return \'id\';'))
	)
);

$string = '3*(5+2)';

$lexer = new Lexer($lexerConfig);
$parser = new Parser(new SLR($parserConfig));

$tokens = $lexer->lex($string);

$parser->parse($tokens);