<?php
/**
 * UnrecognizedTokenException exception.
 *
 * PHP version 5.2
 *
 * @category SLR
 * @package  SLR\Lexer\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Lexer\Exception;

/**
 * Exception for when no lexer rule could have matched some part of input.
 *
 * @category SLR
 * @package  SLR\Lexer\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class UnrecognizedTokenException extends AbsLexerException
{
    /**
     * String that couldn't have been matched.
     *
     * @var string $string
     */
    protected $string;

    /**
     * Creates new unrecognzied token exception.
     *
     * @param int        $row      Row in which the exception occured
     * @param int        $column   Column in which the exception occured
     * @param string     $string   String that couldn't have been matched
     * @param int        $code     Exception code
     * @param \Exception $previous Previous exception used for exception chaining
     */
    public function __construct(
        $row, $column, $string, $code = 0, \Exception $previous = null
    ) {
        parent::__construct(
            $row, $column, "Unrecognized token: \"{$string}\"", $code, $previous
        );
        $this->string = $string;
    }

    /**
     * Returns string that couldn't have been matched.
     *
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }
}