<?php
/**
 * Unrecognized token class.
 *
 * PHP version 5.2
 *
 * @category SLR
 * @package  Tokens
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

/**
 * Unrecognized token class. Used to represent tokens that have not been recognized.
 *
 * @category SLR
 * @package  Tokens
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class SLR_Elements_Tokens_Unrecognized extends SLR_Elements_Tokens_Token
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
     * @param mixed  $value  token's value
     * @param string $state  lexer's state that token was captured in
     * @param int    $row    input row that token was captured in
     * @param int    $column input column that token was captured in
     */
    public function __construct(
        $value = null, $state = null, $row = null, $column = null
    ) {
        parent::__construct(self::TOKEN_NAME, $value, $state, $row, $column);
    }
}