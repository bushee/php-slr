<?php
/**
 * AbsAction class.
 *
 * PHP version 5.2
 *
 * @category SLR
 * @package  SLR\Parser\Actions
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Parser\Actions;

use SLR\Parser\SLRTable;

/**
 * Abstract action class. Used to provide common interface for all actions available
 * in parser's SLR table.
 *
 * @category SLR
 * @package  SLR\Parser\Actions
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
abstract class AbsAction
{
    /**
     * SLR table this action is used in.
     *
     * @var SLRTable $slr
     */
    protected $slr;
    /**
     * Action parameter; actual meaning depends on action itself.
     *
     * @var mixed
     */
    protected $param;

    /**
     * Common action constructor.
     *
     * @param SLRTable $slr   SLR table this action is used in
     * @param mixed    $param Action parameter; actual meaning depends on action
     */
    public function __construct(SLRTable $slr, $param)
    {
        $this->slr = $slr;
        $this->param = $param;
    }

    /**
     * Returns action's parameter.
     *
     * @return mixed
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * Returns human-readable action type string.
     *
     * @return string
     */
    abstract public function getType();
    /**
     * Returns action prefix, mostly for SLR table printing purpose.
     *
     * @return string
     */
    abstract protected function prefix();
    /**
     * Performs actual action.
     * This method is passed current parsing stack and input stream, and is allowed
     * to modify them both.
     * Returns either int, or boolean true. The earlier value means that parser is
     * asked to perform transition to state of that ID, while the latter - that
     * input string has been accepted and parser should finish its work.
     *
     * @param array &$stack Current parsing stack
     * @param array &$input Remaining input stream
     *
     * @return int|bool
     */
    abstract public function execute(array &$stack, array &$input);

    /**
     * Returns detailed action representation, consisting of action prefix
     * and carried parameter. It is used mostly for SLR table printing purpose.
     *
     * @return string
     *
     * @see AbsAction::prefix
     */
    public function __toString()
    {
        return $this->prefix() . $this->param;
    }
}