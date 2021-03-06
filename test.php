<?php
/**
 * Test file.
 *
 * PHP version 5.3
 *
 * @category SLR
 * @package  Core
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
require_once 'slr.php';

// TODO pretty classes for parser config, ruleset and rules
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
                array('NUM'),
                create_function('$v', 'return $v[0];')
            )
        ),
        'NUM' => array(
            array(
                array('digit', 'NUM'),
                create_function('$v', 'return $v[0] . $v[1];')
            ),
            array(
                array(), // TODO proper handling of empty rules
            )
        )
    ),
    'success' => create_function('$v', 'echo "$v\n"; return $v;')
);

// TODO pretty classes for lexer config and rules
// TODO operator precedence routines
$lexerConfig = array(
    'initial' => array(
        array('string', '+', create_function('&$value', 'return \'+\';')),
        array('string', '*', create_function('&$value', 'return \'x\';')),
        array('string', '(', create_function('&$value', 'return \'(\';')),
        array('string', ')', create_function('&$value', 'return \')\';')),
        array('regex', '/[0-9]/', create_function('&$value', 'return \'digit\';'))
    )
);

$string = '3*(5+2)+13*2+1';

$lexer = new SLR\Lexer\Lexer($lexerConfig);
$slr = new SLR\Parser\SLRTable($parserConfig);
$parser = new SLR\Parser\Parser($slr);

$tokens = $lexer->lex($string);
$lexerCaretPosition = $lexer->getCaretPosition();

$parser->parse($tokens, $lexerCaretPosition['row']);
