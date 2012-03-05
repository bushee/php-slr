<?php
/**
 * Unrecognized token class.
 *
 * PHP version 5.2.todo
 *
 * @category Tokens
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */

/**
 * Unrecognized token class. Used to represent tokens that have not been recognized.
 *
 * @category Tokens
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
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
     * @param mixed  $value token's value
     * @param string $state lexer's state that token was captured in
     */
    public function __construct($value = null, $state = null)
    {
        parent::__construct(self::TOKEN_NAME, $value, $state);
    }
}