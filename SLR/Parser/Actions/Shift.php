<?php
/**
 * Shift class.
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

/**
 * Shift action class. Represents action taken when some terminal token is being
 * shifted, leading to parser state change.
 * In fact, this class is identical (except for type and prefix, which have no
 * actual impact on parser's work) to SLR\Parser\Actions\Transition, with only
 * exception that shift may occur only upon reaching some terminal token, while
 * transition - only upon reaching non-terminal token.
 *
 * @category SLR
 * @package  SLR\Parser\Actions
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 * @see      SLR\Parser\Actions\Transition
 */
class Shift extends AbsAction
{
    /**
     * Returns human-readable action type string.
     *
     * @return string
     */
    public function getType()
    {
        return 'shift';
    }

    /**
     * Returns action prefix, mostly for SLR table printing purpose.
     *
     * @return string
     */
    protected function prefix()
    {
        return 's';
    }

    /**
     * Performs actual action - shifts first terminal token from input stream and
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
