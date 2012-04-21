<?php
/**
 * Epsilon token class.
 *
 * PHP version 5.2.todo
 *
 * @category SLR
 * @package  Tokens
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

/**
 * Epsilon token class. This special token represents empty right side of rule.
 *
 * @category SLR
 * @package  Tokens
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class SLR_Elements_Tokens_Epsilon extends SLR_Elements_Tokens_Token
{
    /**
     * Epsilon token's token name.
     *
     * @const string TOKEN_NAME
     */
    const TOKEN_NAME = '<epsilon>';

    /**
     * Creates epsilon token.
     */
    public function __construct()
    {
        parent::__construct(self::TOKEN_NAME);
    }
}