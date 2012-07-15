<?php
/**
 * End token class.
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
 * End token class. This special token is always added to the end of input token
 * stream to be consumed by rules recognizing end of stream.
 *
 * @category SLR
 * @package  SLR\Parser\Elements\Tokens
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class End extends Token
{
    /**
     * End token's token name.
     *
     * @const string TOKEN_NAME
     */
    const TOKEN_NAME = 'T_EOF';

    /**
     * Creates end token.
     *
     * @param int $row    Input row that token was captured in
     * @param int $column Input column that token was captured in
     */
    public function __construct($row = null, $column = null)
    {
        parent::__construct(self::TOKEN_NAME, null, null, $row, $column);
    }
}