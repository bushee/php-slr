<?php
/**
 * MatcherNotFoundException exception.
 *
 * PHP version 5.2
 *
 * @category   SLR
 * @package    Matchers
 * @subpackage Exceptions
 * @author     Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       http://bushee.ovh.org
 */

/**
 * Exception for when there was a trial to instantiate a matcher of unknown type.
 *
 * @category   SLR
 * @package    Matchers
 * @subpackage Exceptions
 * @author     Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       http://bushee.ovh.org
 */
class SLR_Matchers_MatcherNotFoundException extends Exception
{
    /**
     * Erroneous matcher type.
     *
     * @var string $type
     */
    protected $type;

    /**
     * Creates new matcher not found exception.
     *
     * @param string    $type     erroneous matcher type
     * @param int       $code     the exception code
     * @param Exception $previous the previous exception used for the
     *                                     exception chaining
     */
    public function __construct(
        $type, $code = 0, $previous = null
    ) {
        parent::__construct("Matcher \"$type\" doesn't exist.", $code, $previous);
        $this->type = $type;
    }

    /**
     * Returns erroneous matcher type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}