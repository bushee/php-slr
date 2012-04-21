<?php
/**
 * UnexpectedEndOfInputException exception.
 *
 * PHP version 5.2
 *
 * @category   SLR
 * @package    Core
 * @subpackage Exceptions
 * @author     Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       http://bushee.ovh.org
 */

/**
 * Exception for when input token stream ends unexpectedly, while parser was assuming
 * to get some more tokens.
 *
 * @category   SLR
 * @package    Core
 * @subpackage Exceptions
 * @author     Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       http://bushee.ovh.org
 */
class SLR_UnexpectedEndOfInputException extends SLR_AbsParserException
{
    /**
     * Creates new unexpected end of input exception.
     *
     * @param SLR_Elements_Tokens_Token $token    token that caused exception
     * @param array                     $expected list of expected tokens
     * @param int                       $code     the exception code
     * @param Exception                 $previous the previous exception used for
     *                                            the exception chaining
     */
    public function __construct(
        $token, $expected, $code = 0, $previous = null
    ) {
        $message = 'Unexpected end of input on line ' . $token->getRow() . '.';
        parent::__construct($token, $expected, $message, $code, $previous);
    }
}