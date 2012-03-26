<?php
/**
 * AbsParserException exception.
 *
 * PHP version 5.2.todo
 *
 * @category Exceptions
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */

/**
 * Abstract parser exception for deriving any parsing-based exceptions.
 *
 * @category Exceptions
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */
class SLR_AbsParserException extends Exception
{
    /**
     * Token that caused exception.
     *
     * @var SLR_Elements_Tokens_Token $token
     */
    protected $token;
    /**
     * List of tokens that were expected instead of problematic token.
     *
     * @var array $expectedTokens
     */
    protected $expectedTokens;

    /**
     * Creates new parser exception.
     *
     * @param SLR_Elements_Tokens_Token $token    token that caused exception
     * @param array                     $expected list of expected tokens
     * @param string                    $message  the exception message to throw
     * @param int                       $code     the exception code
     * @param Exception                 $previous the previous exception used for
     *                                            the exception chaining
     */
    public function __construct(
        $token, $expected, $message = '', $code = 0, $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->token = $token;
        $this->expectedTokens = $expected;
    }

    /**
     * Returns token that caused exception.
     *
     * @return SLR_Elements_Tokens_Token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Returns list of tokens that were expected instead of problematic token.
     * Obviously, list of _token names_, and not actual tokens, is returned.
     *
     * @return array
     */
    public function getExpectedTokens()
    {
        return $this->expectedTokens;
    }
}