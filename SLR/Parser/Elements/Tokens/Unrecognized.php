<?php
/**
 * Unrecognized token class.
 *
 * PHP version 5.2
 *
 * @category SLR
 * @package  SLR\Parser\Elements\Tokens
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Parser\Elements\Tokens;

/**
 * Unrecognized token class. Used to represent tokens that have not been recognized.
 *
 * @category SLR
 * @package  SLR\Parser\Elements\Tokens
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class Unrecognized extends Token
{
    /**
     * Unrecognized token's token name.
     *
     * @const string TOKEN_NAME
     */
    const TOKEN_NAME = 'T_UNRECOGNIZED_TOKEN';

    /**
     * Creates unrecognized token.
     *
     * @param mixed  $value  Token's value
     * @param string $state  Lexer's state that token was captured in
     * @param int    $row    Input row that token was captured in
     * @param int    $column Input column that token was captured in
     */
    public function __construct(
        $value = null, $state = null, $row = null, $column = null
    ) {
        parent::__construct(self::TOKEN_NAME, $value, $state, $row, $column);
    }
}