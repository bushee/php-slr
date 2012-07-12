<?php
/**
 * UnexpectedTokenException exception.
 *
 * PHP version 5.2
 *
 * @category SLR
 * @package  SLR\Parser\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Parser\Exception;

use SLR\Parser\Elements\Tokens\Token;

/**
 * Exception for when token of given type wasn't expected at the moment.
 *
 * @category SLR
 * @package  SLR\Parser\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class UnexpectedTokenException extends AbsParserException
{
    /**
     * Creates new unexpected token exception.
     *
     * Exception message may optionally contain list of expected tokens, if their
     * count doesn't exceed $expLimit. If $expLimit is 0, this list will never be
     * passed. On the other hand, if boolean true is given, the list will be passed
     * no matter the size.
     *
     * @param Token      $token    Token that caused exception
     * @param array      $expected List of expected tokens
     * @param int|bool   $expLimit Max. amount of expected tokens to be included in
     *                             exception message
     * @param int        $code     Exception code
     * @param \Exception $previous Previous exception used for exception chaining
     */
    public function __construct(
        Token $token, array $expected, $expLimit = 1, $code = 0,
        \Exception $previous = null
    ) {
        $message = 'Unexpected ' . $token->getType();
        if (($expLimit === true) || (count($expected) <= $expLimit)) {
            $message .= ', expecting ';
            if (count($expected) == 1) {
                $message .= array_shift($expected);
            } else {
                $last = array_pop($expected);
                $message .= implode(', ', $expected) . ' or ' . $last;
            }
        }

        $message .= ' on line ' . $token->getRow()
            . ', column ' . $token->getColumn() . '.';

        parent::__construct($token, $expected, $message, $code, $previous);
    }
}