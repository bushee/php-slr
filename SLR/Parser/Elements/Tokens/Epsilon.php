<?php
/**
 * Epsilon token class.
 *
 * PHP version 5.3
 *
 * @category SLR
 * @package  SLR\Parser\Elements\Tokens
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Parser\Elements\Tokens;

/**
 * Epsilon token class. This special token represents empty right side of rule.
 *
 * @category SLR
 * @package  SLR\Parser\Elements\Tokens
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 *
 * TODO not sure whether epsilon token makes sense at all
 */
class Epsilon extends Token
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