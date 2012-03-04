<?php
class SLR_Elements_State
{
    protected $id;
    protected $set;
    protected $transitions;

    protected static $count = 0;

    public function __construct($set)
    {
        $this->id = self::$count ++;
        $this->set = $set;
        $this->transitions = array();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSet()
    {
        return $this->set;
    }

    public function addTransition($token, $state)
    {
        if (isset($this->transitions[$token])) {
            throw new Exception("This state already has a transition for token '$token'.");
        }

        $this->transitions[$token] = $state;
    }

    public function getTransitions()
    {
        return $this->transitions;
    }

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