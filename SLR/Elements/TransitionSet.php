<?php
class SLR_Elements_TransitionSet implements Iterator
{
    protected $states;
    protected $stateIds;
    protected $startState;

    public function __construct(&$slr)
    {
        $this->states = array();
        $this->stateIds = array();

        $situation = new SLR_Elements_Situation($slr, 0, 0);
        $closure = $situation->closure();

        $this->startState = $this->addState($closure);

        foreach ($this as $state) {
            $set = $state->getSet();
            foreach ($set->nextTokens() as $next) {
                $state->addTransition($next, $this->addState($set->transition($next)));
            }
        }
    }

    protected function addState($situationSet, $addState = true)
    {
        $id = false;

        if (isset($this->stateIds[$situationSet->getKey()])) {
            $id = $this->stateIds[$situationSet->getKey()];
        } elseif ($addState) {
            $state = new SLR_Elements_State($situationSet);
            $id = $state->getId();

            $this->states[$id] = $state;
            $this->stateIds[$situationSet->getKey()] = $id;
        }

        return $id;
    }

    public function getStartState()
    {
        return $this->startState;
    }

    public function current()
    {
        return current($this->states);
    }

    public function key()
    {
        return key($this->states);
    }

    public function next()
    {
        next($this->states);
    }

    public function rewind()
    {
        reset($this->states);
    }

    public function valid()
    {
        return key($this->states) !== null;
    }

    public function __toString()
    {
        $ret = '';

        foreach ($this->states as $state)
        {
            $ret .= "$state\n";
        }

        return $ret;
    }
}
