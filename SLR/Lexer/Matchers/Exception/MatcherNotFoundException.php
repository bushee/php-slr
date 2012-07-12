<?php
/**
 * MatcherNotFoundException exception.
 *
 * PHP version 5.2
 *
 * @category SLR
 * @package  SLR\Lexer\Matchers\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Lexer\Matchers\Exception;

/**
 * Exception for when there was a trial to instantiate a matcher of unknown type.
 *
 * @category SLR
 * @package  SLR\Lexer\Matchers\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class MatcherNotFoundException extends \Exception
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
     * @param string     $type     Erroneous matcher type
     * @param int        $code     Exception code
     * @param \Exception $previous Previous exception used for exception chaining
     */
    public function __construct(
        $type, $code = 0, \Exception $previous = null
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