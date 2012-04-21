<?php
/**
 * Accept class.
 *
 * PHP version 5.2
 *
 * @category SLR
 * @package  Actions
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

/**
 * Accept action class. Represents action taken at the end of parsing process,
 * meaning that input stream has been accepted.
 *
 * @category SLR
 * @package  Actions
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class SLR_Actions_Accept extends SLR_Actions_AbsAction
{
	/**
	 * Creates new accept action.
	 */
    public function __construct()
    {
    }

    /**
     * Returns human-readable action type string.
     *
     * @return string
     */
    public function getType()
    {
        return 'accept';
    }

    /**
     * Returns empty string, since this method is never meant to be used, and is
     * here only to comply parent class' interface.
     *
     * @return string
     */
    protected function prefix()
    {
        return '';
    }

    /**
     * Does nothing and simply returns true. Whenever parser was given this object
     * and calls this method, it is informed that parsed input has been accepted
     * and to end its work.
     *
     * @param array &$stack current parsing stack
     * @param array &$input remaining input stream
     *
     * @return true
     */
    public function execute(&$stack, &$input)
    {
        return true;
    }

    /**
     * Returns detailed action representation, consisting of action prefix
     * and carried parameter. It is used mostly for SLR table printing purpose.
     *
     * @return string
     */
    public function __toString()
    {
        return 'ACC';
    }
}