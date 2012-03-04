<?php
class SLR_Elements_Situation
{
    public $slr;
    protected $rule;
    protected $dot;

    public function __construct(&$slr, $rule, $dot = 0)
    {
        $count = $slr->ruleLength($rule);
        if ($dot > $count) {
            throw new Exception("Dot may be on positions 0-$count in $count-token rule; $dot given.");
        }

        $this->slr = $slr;
        $this->rule = $rule;
        $this->dot = $dot;
    }

    public function getKey()
    {
        return $this->rule . '.' . $this->dot;
    }

    public function getRuleId()
    {
        return $this->rule;
    }

    public function getRule()
    {
        return $this->slr->rule($this->rule);
    }

    public function hasNext()
    {
        return $this->dot < $this->slr->ruleLength($this->rule);
    }

    public function next()
    {
        if (!$this->hasNext()) {
            return null;
        } else {
            $rule = $this->slr->rule($this->rule);
            return $rule['right'][$this->dot];
        }
    }

    public function step()
    {
        if (!$this->hasNext()) {
            return false;
        } else {
            return new self($this->slr, $this->rule, $this->dot + 1);
        }
    }

    public function __toString()
    {
        $rule = $this->slr->rule($this->rule);
        $count = count($rule['right']);

        $ret = array($rule['left'], '->');
        for ($i = 0; $i < $count; ++ $i) {
            if ($this->dot == $i) {
                $ret[] = '*';
            }
            $ret[] = $rule['right'][$i];
        }
        if ($this->dot == $count) {
            $ret[] = '*';
        }

        return implode(' ', $ret);
    }

    public function closure()
    {
        $set = new SLR_Elements_SituationSet();
        $set->add($this);
        return $set->closure();
    }

    public function transition($token)
    {
        $set = new SLR_Elements_SituationSet();
        $set->add($this);
        return $set->transition($token);
    }
}
