<?php
/**
 * AbsAction class.
 *
 * PHP version 5.2.todo
 *
 * @category Actions
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */

/**
 * Abstract action class. Used to provide common interface for all actions available
 * in parser's SLR table.
 *
 * @category Actions
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */
abstract class SLR_Actions_AbsAction
{
    /**
     * SLR instance this action would happen for.
     *
     * @var SLR_SLR $slr
     */
    protected $slr;
    /**
     * Action parameter; actual meaning depends on action itself.
     *
     * @var unknown_type
     */
    protected $param;

    /**
     * Common action constructor.
     *
     * @param SLR_SLR $slr   SLR instance this action would happen for
     * @param mixed   $param action parameter; actual meaning depends on action
     */
    public function __construct($slr, $param)
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
     * @param array &$stack current parsing stack
     * @param array &$input remaining input stream
     *
     * @return bool|int
     */
    abstract public function execute(&$stack, &$input);

    /**
     * Returns detailed action representation, consisting of action prefix
     * and carried parameter. It is used mostly for SLR table printing purpose.
     *
     * @return string
     *
     * @see SLR_Actions_AbsAction::prefix
     */
    public function __toString()
    {
        return $this->prefix() . $this->param;
    }
}