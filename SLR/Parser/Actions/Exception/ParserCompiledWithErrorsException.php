<?php
/**
 * ParserCompiledWithErrorsException exception.
 *
 * PHP version 5.3
 *
 * @category SLR
 * @package  SLR\Parser\Actions\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Parser\Actions\Exception;

/**
 * Exception for when there was discovered a situation suggesting that parser has
 * been compiled with some errors.
 *
 * @category SLR
 * @package  SLR\Parser\Actions\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class ParserCompiledWithErrorsException extends \Exception
{
    /**
     * Creates new parsed compiled with errors exception.
     *
     * @param string     $message  Exception message to throw
     * @param int        $code     Exception code
     * @param \Exception $previous Previous exception used for exception chaining
     */
    public function __construct(
        $message = '', $code = 0, \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}