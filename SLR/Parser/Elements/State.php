<?php
/**
 * State class.
 *
 * PHP version 5.2
 *
 * @category SLR
 * @package  SLR\Parser\Elements
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Parser\Elements;

use SLR\Parser\Elements\Exception\TransitionAlreadyExistsException;

/**
 * State class. Represents a single state in SLR table.
 *
 * @category SLR
 * @package  SLR\Parser\Elements
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class State
{
    /**
     * State ID. This value SHOULD be unique, at least for each SLR table.
     *
     * @var int $id
     */
    protected $id;
    /**
     * Set of situations in which parser is in current state.
     *
     * @var SituationSet $set
     */
    protected $set;
    /**
     * List of transitions possible in current state. It is array in which keys are
     * tokens that could be consumed to perform transition, and values are states to
     * which parser will switch after doing so.
     *
     * @var array $transitions
     */
    protected $transitions;

    /**
     * Global count of State objects; it is used only to assign unique IDs for each
     * subsequent object.
     *
     * @var int $count
     */
    protected static $count = 0;

    /**
     * Cretes new state instance.
     *
     * @param SituationSet $set Situation set representing this state
     */
    public function __construct(SituationSet $set)
    {
        $this->id = self::$count ++;
        $this->set = $set;
        $this->transitions = array();
    }

    /**
     * Returns state ID.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns set of situations that parser could be in while being in this state.
     *
     * @return SituationSet
     */
    public function getSet()
    {
        return $this->set;
    }

    /**
     * Adds new transition possible in state.
     *
     * @param string $token Token to be consumed to perform transition
     * @param int    $state ID of state in which parser will be after doing so
     *
     * @return void
     *
     * @throws TransitionAlreadyExistsException If transition that was to be added
     *                                          is already specified for this state
     */
    public function addTransition($token, $state)
    {
        if (isset($this->transitions[$token])) {
            throw new TransitionAlreadyExistsException($token, $this);
        }

        $this->transitions[$token] = $state;
    }

    /**
     * Returns list of all transitions possible in this state.
     *
     * @return array
     */
    public function getTransitions()
    {
        return $this->transitions;
    }

    /**
     * Returns human-readable representation of state.
     * It consists of information on state ID, list of situations in which parser
     * could be while being in this state, and list of all tokens that could be
     * consumed further, completed with ID of state in which parser will be after
     * doing so.
     *
     * @return string
     */
    public function __toString()
    {
        $ret = 'state ' . $this->id . ":\n";
        $ret .= $this->set;
        $ret .= "transitions:\n";
        foreach ($this->transitions as $token => $next) {
            $ret .= "$token -> $next\n";
        }
        $ret .= "\n";
        return $ret;
    }
}