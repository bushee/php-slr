<?php
/**
 * UnexpectedEndOfInputException exception.
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
 * Exception for when input token stream ends unexpectedly, while parser was assuming
 * to get some more tokens.
 *
 * @category SLR
 * @package  SLR\Parser\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class UnexpectedEndOfInputException extends AbsParserException
{
    /**
     * Creates new unexpected end of input exception.
     *
     * @param Token      $token    Token that caused exception
     * @param array      $expected List of expected tokens
     * @param int        $code     Exception code
     * @param \Exception $previous Previous exception used for exception chaining
     */
    public function __construct(
        Token $token, array $expected, $code = 0, \Exception $previous = null
    ) {
        $message = 'Unexpected end of input on line ' . $token->getRow() . '.';
        parent::__construct($token, $expected, $message, $code, $previous);
    }
}