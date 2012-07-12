<?php
/**
 * UnknownBorderTypeException exception.
 *
 * PHP version 5.2
 *
 * @category SLR
 * @package  SLR\Utils\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Utils\Exception;

/**
 * Exception for when user is trying to specify unknown border type.
 *
 * @category SLR
 * @package  SLR\Utils\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class UnknownBorderTypeException extends \Exception
{
    /**
     * Creates new unknown border type exception.
     *
     * @param string     $borderType Border type that was unknown
     * @param int        $code       Exception code
     * @param \Exception $previous   Previous exception used for exception chaining
     */
    public function __construct($borderType, $code = 0, \Exception $previous = null)
    {
        parent::__construct("Unknown border type: {$borderType}.", $code, $previous);
    }
}