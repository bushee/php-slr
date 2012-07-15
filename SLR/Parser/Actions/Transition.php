<?php
/**
 * Transition class.
 *
 * PHP version 5.3
 *
 * @category SLR
 * @package  SLR\Parser\Actions
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Parser\Actions;

/**
 * Transition action class. Represents action taken when some non-terminal token is
 * being shifted, leading to parser state change.
 * In fact, this class is identical (except for type and prefix, which have no
 * actual impact on parser's work) to SLR\Parser\Actions\Shift, with only exception
 * that transition may occur only upon reaching some non-terminal token, while shift
 * - only upon reaching terminal token.
 *
 * @category SLR
 * @package  SLR\Parser\Actions
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 * @see      SLR\Parser\Actions\Shift
 */
class Transition extends AbsAction
{
    /**
     * Returns human-readable action type string.
     *
     * @return string
     */
    public function getType()
    {
        return 'transition';
    }

    /**
     * Returns empty string, due to fact that this method is used mostly for SLR
     * table printing purpose, and transitions are represented by new state ID only.
     *
     * @return string
     */
    protected function prefix()
    {
        return '';
    }

    /**
     * Performs actual action - shifts first non-terminal token from input stream and
     * pops it to parsing stack. After that, state ID to which parser should switch
     * is both popped to parsing stack and returned.
     *
     * @param array &$stack Current parsing stack
     * @param array &$input Remaining input stream
     *
     * @return int
     */
    public function execute(array &$stack, array &$input)
    {
        $stack[] = array_shift($input);
        $stack[] = $this->param;
        return $this->param;
    }
}
