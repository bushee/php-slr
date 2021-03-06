<?php
/**
 * TransitionSet class.
 *
 * PHP version 5.3
 *
 * @category SLR
 * @package  SLR\Parser\Elements
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Parser\Elements;

use SLR\Parser\SLRTable;

/**
 * Transition set class.
 * Class implements Iterator interface for convenient iteration purpose. It will
 * iterate over all states, in order of their addition (probably in ascending order
 * of their IDs).
 *
 * @category SLR
 * @package  SLR\Parser\Elements
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class TransitionSet implements \Iterator
{
    /**
     * List of all possible states, keyed with their IDs.
     *
     * @var array $states
     */
    protected $states;
    /**
     * List of states' IDs, keyed with state keys.
     *
     * @var array $stateIds
     */
    protected $stateIds;
    /**
     * ID of start state.
     *
     * @var int $startState
     */
    protected $startState;

    /**
     * Creates new transition set. It will be populated with all possible transitions
     * for given SLR table.
     *
     * @param SLRTable $slr SLR table to calculate transitions for
     */
    public function __construct(SLRTable $slr)
    {
        $this->states = array();
        $this->stateIds = array();

        $situation = new Situation($slr, 0, 0);
        $closure = $situation->closure();

        $this->startState = $this->addState($closure);

        /* Here it is important to iterate on $this instead of $this->states, which
           may seem tempting, since practically these two operations are the same.
           However, iterating of $this->states array "freezes" its contents in state
           they were at the beginning of foreach loop, while there are new elements
           added to the array in most iterations, and they should be iterated over
           too - which iterating on $this does, because instead of freezing array
           state, proper methods are called. */
        /** @var State $state */
        foreach ($this as $state) {
            $set = $state->getSet();
            foreach ($set->nextTokens() as $next) {
                $state->addTransition(
                    $next, $this->addState($set->transition($next))
                );
            }
        }
    }

    /**
     * Tries to add new state for given situation set. If such situation set is
     * already known, no change is done. If it is unknown yet, however, and $addState
     * flag is turned off, nothing will be done. In such case, method will return
     * boolean false; in any other case, resulting state ID is returned.
     *
     * @param SituationSet $situationSet Situation set to add state for
     * @param bool         $addState     Add state if it is unknown yet?
     *
     * @return int|bool
     */
    protected function addState(SituationSet $situationSet, $addState = true)
    {
        $id = false;

        if (isset($this->stateIds[$situationSet->getKey()])) {
            $id = $this->stateIds[$situationSet->getKey()];
        } elseif ($addState) {
            $state = new State($situationSet);
            $id = $state->getId();

            $this->states[$id] = $state;
            $this->stateIds[$situationSet->getKey()] = $id;
        }

        return $id;
    }

    /**
     * Returns start state ID.
     *
     * @return int
     */
    public function getStartState()
    {
        return $this->startState;
    }

    /**
     * Returns current element for iteration purpose.
     *
     * @return State
     *
     * @see \Iterator::current
     */
    public function current()
    {
        return current($this->states);
    }

    /**
     * Returns current element's key for iteration purpose.
     *
     * @return int
     *
     * @see \Iterator::key
     */
    public function key()
    {
        return key($this->states);
    }

    /**
     * Moves iteration to next element.
     *
     * @return void
     *
     * @see \Iterator::next
     */
    public function next()
    {
        next($this->states);
    }

    /**
     * Rewinds iteration process.
     *
     * @return void
     *
     * @see \Iterator::rewind
     */
    public function rewind()
    {
        reset($this->states);
    }

    /**
     * Checks whether there are any elements left for iteration purpose.
     *
     * @return bool
     *
     * @see \Iterator::valid
     */
    public function valid()
    {
        return key($this->states) !== null;
    }

    /**
     * Returns human-readable transition set representation. It consists of list of
     * human readable representation of each state.
     *
     * @return string
     *
     * @see State::__toString
     */
    public function __toString()
    {
        return implode("\n", $this->states);
    }
}
