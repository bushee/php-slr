<?php
/**
 * UnexpectedTokenException exception.
 *
 * PHP version 5.2.todo
 *
 * @category   SLR
 * @package    Core
 * @subpackage Exceptions
 * @author     Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       http://bushee.ovh.org
 */

/**
 * Exception for when token of given type wasn't expected at the moment.
 *
 * @category   SLR
 * @package    Core
 * @subpackage Exceptions
 * @author     Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       http://bushee.ovh.org
 */
class SLR_UnexpectedTokenException extends SLR_AbsParserException
{
    /**
     * Creates new unexpected token exception.
     *
     * Exception message may optionally contain list of expected tokens, if their
     * count doesn't exceed $expLimit. If $expLimit is 0, this list will never be
     * passed. On the other hand, if boolean true is given, the list will be passed
     * no matter the size.
     *
     * @param SLR_Elements_Tokens_Token $token    token that caused exception
     * @param array                     $expected list of expected tokens
     * @param int|bool                  $expLimit max. amount of expected tokens to
     *                                            be included in exception message
     * @param int                       $code     the exception code
     * @param Exception                 $previous the previous exception used for
     *                                            the exception chaining
     */
    public function __construct(
        $token, $expected, $expLimit = 1, $code = 0, $previous = null
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

        $message .= '.';

        parent::__construct($token, $expected, $message, $code, $previous);
    }
}