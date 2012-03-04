<?php
class SLR_Elements_SituationSet implements ArrayAccess, Iterator
{
    protected $set;
    protected $key;
    protected $current;

    public function __construct()
    {
        $this->set = array();
        $this->invalidateKey();
    }

    public function offsetExists($offset)
    {
        return isset($this->set[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->set[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->invalidateKey();
        return $this->set[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->set[$offset]);
    }

    public function add($situation)
    {
        $key = $situation->getKey();
        if (isset($this->set[$key])) {
            return false;
        } else {
            $this->invalidateKey();
            $this->set[$key] = $situation;
            return true;
        }
    }

    public function current()
    {
        return current($this->set);
    }

    public function key()
    {
        return key($this->set);
    }

    public function next()
    {
        next($this->set);
    }

    public function rewind()
    {
        reset($this->set);
    }

    public function valid()
    {
        return key($this->set) !== null;
    }

    public function closure()
    {
        $situations = $this->set;
        $set = new self();

        while (!empty($situations)) {
            $situation = array_shift($situations);
            if ($set->add($situation)) {
                $slr = &$situation->slr;
                foreach ($slr->rulesOf($situation->next()) as $id) {
                    $situations[] = new SLR_Elements_Situation($slr, $id, 0);
                }
            }
        }

        return $set;
    }

    public function transition($token)
    {
        $set = new self();

        foreach ($this->set as $situation) {
            if ($situation->next() == $token) {
                $next = $situation->step();
                if ($next) {
                    $set->add($next);
                }
            }
        }

        return $set->closure();
    }

    public function nextTokens()
    {
        $next = array();

        foreach ($this->set as $situation) {
            $token = $situation->next();
            if (isset($token)) {
                $next[$token] = $token;
            }
        }

        return $next;
    }

    public function equals($set)
    {
        return $this->getKey() == $set->getKey();
    }

    public function getKey()
    {
        if (!isset($this->key)) {
            $keys = array();
            foreach ($this->set as $situation) {
                $key = $situation->getKey();
                $keys[$key] = $key;
            }
            sort($keys);
            $this->key = implode('|', $keys);
        }

        return $this->key;
    }

    public function invalidateKey()
    {
        $this->key = null;
    }

    public function __toString()
    {
        $ret = '';

        foreach ($this->set as $situation) {
            $ret .= "$situation\n";
        }

        return $ret;
    }
}
