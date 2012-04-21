<?php
/**
 * ParserCompiledWithErrorsException exception.
 *
 * PHP version 5.2.todo
 *
 * @category   SLR
 * @package    Actions
 * @subpackage Exceptions
 * @author     Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       http://bushee.ovh.org
 */

/**
 * Exception for when there was discovered a situation suggesting that parser has
 * been compiled with some errors.
 *
 * @category   SLR
 * @package    Actions
 * @subpackage Exceptions
 * @author     Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       http://bushee.ovh.org
 */
class SLR_Actions_ParserCompiledWithErrorsException extends Exception
{
    /**
     * Creates new parsed compiled with errors exception.
     *
     * @param string    $message  the exception message to throw
     * @param int       $code     the exception code
     * @param Exception $previous the previous exception used for the exception
     *                            chaining
     */
    public function __construct($message = '', $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}