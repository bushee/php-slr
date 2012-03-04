<?php
/**
 * UnknownBorderTypeException exception.
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
 * Exception for when user is trying to specify unknown border type.
 *
 * @category Exceptions
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */
class SLR_Utils_UnknownBorderTypeException extends Exception
{
    /**
     * Creates new unknown border type exception.
     *
     * @param string    $borderType the border type that was unknown
     * @param int       $code       the exception code
     * @param Exception $previous   the previous exception used for the exception
     *                              chaining
     *
     * @return string
     */
    public function __construct($borderType, $code = 0, $previous = null)
    {
        parent::__construct("Unknown border type: {$borderType}.", $code, $previous);
    }
}