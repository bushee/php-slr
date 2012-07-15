<?php
/**
 * AbsParserException exception.
 *
 * PHP version 5.3
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
 * Abstract parser exception for deriving any parsing-based exceptions.
 *
 * @category SLR
 * @package  SLR\Parser\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class AbsParserException extends \Exception
{
    /**
     * Token that caused exception.
     *
     * @var Token $token
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
     * @param Token      $token    Token that caused exception
     * @param array      $expected List of expected tokens
     * @param string     $message  Eexception message to throw
     * @param int        $code     Exception code
     * @param \Exception $previous Previous exception used for exception chaining
     */
    public function __construct(
        Token $token, array $expected, $message = '', $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->token = $token;
        $this->expectedTokens = $expected;
    }

    /**
     * Returns token that caused exception.
     *
     * @return Token
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