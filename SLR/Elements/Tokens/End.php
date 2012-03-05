<?php
/**
 * End token class.
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
 * End token class. This special token is always added to the end of input token
 * stream to be consumed by rules recognizing end of stream.
 *
 * @category Tokens
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */
class SLR_Elements_Tokens_End extends SLR_Elements_Tokens_Token
{
    /**
     * End token's token name.
     *
     * @const string TOKEN_NAME
     */
    const TOKEN_NAME = '$';

    /**
     * Creates end token.
     */
    public function __construct()
    {
        parent::__construct(self::TOKEN_NAME);
    }
}