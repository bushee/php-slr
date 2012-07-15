<?php
/**
 * InvalidSituationException exception.
 *
 * PHP version 5.3
 *
 * @category SLR
 * @package  SLR\Parser\Elements\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Parser\Elements\Exception;

/**
 * Exception for when invalid state was about to be created; this means that illegal
 * position for dot was chosen for a rule.
 *
 * @category SLR
 * @package  SLR\Parser\Elements\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class InvalidSituationException extends \Exception
{
    /**
     * Length of rule for which the exception occured.
     *
     * @var int $ruleLength
     */
    protected $ruleLength;
    /**
     * Invalid dot position.
     *
     * @var int $dot
     */
    protected $dot;

    /**
     * Creates new invalid situation exception.
     *
     * @param int        $ruleLength Length of rule for which the exception occured
     * @param int        $dot        Invalid dot position
     * @param int        $code       Exception code
     * @param \Exception $previous   Previous exception used for exception chaining
     */
    public function __construct(
        $ruleLength, $dot, $code = 0, \Exception $previous = null
    ) {
        parent::__construct(
            "Dot may be on positions 0-$ruleLength in $ruleLength-token rule; "
            . "$dot given.", $code, $previous
        );
        $this->ruleLength = $ruleLength;
        $this->dot = $dot;
    }

    /**
     * Returns length of rule for which the exception occured.
     *
     * @return int
     */
    public function getRuleLength()
    {
        return $this->ruleLength;
    }

    /**
     * Returns invalid dot position.
     *
     * @return int
     */
    public function getDot()
    {
        return $this->dot;
    }
}