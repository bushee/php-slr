<?php
/**
 * InvalidSituationException exception.
 *
 * PHP version 5.2
 *
 * @category   SLR
 * @package    Elements
 * @subpackage Exceptions
 * @author     Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       http://bushee.ovh.org
 */

/**
 * Exception for when invalid state was about to be created; this means that illegal
 * position for dot was chosen for a rule.
 *
 * @category   SLR
 * @package    Elements
 * @subpackage Exceptions
 * @author     Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       http://bushee.ovh.org
 */
class SLR_Elements_InvalidSituationException extends Exception
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
     * @param int       $ruleLength length of rule for which the exception occured
     * @param int       $dot        invalid dot position
     * @param int       $code       the exception code
     * @param Exception $previous   the previous exception used for the
     *                              exception chaining
     */
    public function __construct(
        $ruleLength, $dot, $code = 0, $previous = null
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