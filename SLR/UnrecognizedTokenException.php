<?php
/**
 * UnrecognizedTokenException exception.
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
 * Exception for when no lexer rule could have matched some part of input.
 *
 * @category Exceptions
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */
class SLR_UnrecognizedTokenException extends SLR_AbsLexerException
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
     * @param int       $row      row in which the exception occured
     * @param int       $column   column in which the exception occured
     * @param string    $string   string that couldn't have been matched
     * @param int       $code     the exception code
     * @param Exception $previous the previous exception used for the exception
     *                            chaining
     */
    public function __construct(
        $row, $column, $string, $code = 0, $previous = null
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