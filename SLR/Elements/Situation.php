<?php
/**
 * Situation class.
 *
 * PHP version 5.2.todo
 *
 * @category Elements
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */

/**
 * Situation class, for representation of situations. Duh!
 * As a situation one may understand a concrete "position" in a rule's right side,
 * meaning that if parser has come to a specific situation, it has already consumed
 * all tokens "to the left" of aforementioned position, and still needs to consume
 * all the tokens "to the right".
 *
 * @category Elements
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */
class SLR_Elements_Situation
{
    /**
     * SLR instance this situation belongs to.
     *
     * @var SLR_SLR $slr
     */
    public $slr;
    /**
     * ID of rule in which this situation "resides".
     *
     * @var int $rule
     */
    protected $rule;
    /**
     * Position of "dot" (the "position") in the rule.
     *
     * @var int $dot
     */
    protected $dot;

    /**
     * Creates new situation for given rule in a SLR instance.
     *
     * @param SLR_SLR $slr  SLR instance the situation belongs to
     * @param int     $rule ID of rule on which the situation will be based
     * @param int     $dot  position of "dot" defining the position; 0 for left-most
     *                      position (meaning that no rule tokens were consumed yet),
     *                      1 = first token was consumed, 2 = two tokens and so on,
     *                      up to [length of rule's right side] meaning that all
     *                      tokens from rule's right side have already been consumed
     */
    public function __construct($slr, $rule, $dot = 0)
    {
        $count = $slr->ruleLength($rule);
        if ($dot > $count) {
            throw new SLR_Elements_InvalidSituationException($count, $dot);
        }

        $this->slr = $slr;
        $this->rule = $rule;
        $this->dot = $dot;
    }

    /**
     * Returns unique situation ID. It helps distinguish two different situations,
     * however it will be always the same for exactly equal situations.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->rule . '.' . $this->dot;
    }

    /**
     * Returns ID of rule this situation is based on.
     *
     * @return int
     */
    public function getRuleId()
    {
        return $this->rule;
    }

    /**
     * Returns the rule this situation is based on.
     *
     * @return array
     */
    public function getRule()
    {
        return $this->slr->rule($this->rule);
    }

    /**
     * Checks whether this situation has any following situations in the same rule.
     * To be so, the only condition is that this situation's "dot" is not further
     * than right before the last possible token (so that it could be moved right
     * after it to become next situation in this case).
     *
     * @return bool
     *
     * @see SLR_Elements_Situation::next
     * @see SLR_Elements_Situation::step
     */
    public function hasNext()
    {
        return $this->dot < $this->slr->ruleLength($this->rule);
    }

    /**
     * Returns token that is to be consumed next in this situation.
     * If this situation has no next token (i.e. the "dot" is positioned after the
     * last possible token), null is returned instead.
     *
     * @return string
     *
     * @see SLR_Elements_Situation::hasNext
     * @see SLR_Elements_Situation::step
     */
    public function next()
    {
        if (!$this->hasNext()) {
            return null;
        } else {
            $rule = $this->slr->rule($this->rule);
            return $rule['right'][$this->dot];
        }
    }

    /**
     * Returns situation available one step further from this one, i.e. a situation
     * after consuming next possible token.
     * When called for situation that has no next token to consume (i.e. the "dot" is
     * positioned after the last possible token), boolean false is returned instead.
     *
     * @return SLR_Elements_Situation|bool
     *
     * @see SLR_Elements_Situation::hasNext
     * @see SLR_Elements_Situation::next
     */
    public function step()
    {
        if (!$this->hasNext()) {
            return false;
        } else {
            return new self($this->slr, $this->rule, $this->dot + 1);
        }
    }

    /**
     * Returns human-readable string representation of this situation.
     * It consists of list of tokens representing the right side of rule on which
     * this situation is based, with an asterisk inserted between some two of them,
     * dividing token set to those already consumed and yet-to-be consumed in the
     * situation.
     *
     * @return string
     */
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

    /**
     * Returns closure of situation set including only this situation.
     * In fact, this method is only a shortcut to closure() method called on a
     * SLR_Elements_SituationSet instance with only this situation inside, hence
     * see mentioned method's documentation for more details.
     *
     * @return SLR_Elements_SituationSet
     *
     * @see SLR_Elements_SituationSet::closure
     */
    public function closure()
    {
        $set = new SLR_Elements_SituationSet();
        $set->add($this);
        return $set->closure();
    }

    /**
     * Returns transition set of situation set including only this situation for
     * a given token.
     * In fact, this method is only a shortcut to transition() method called on a
     * SLR_Elements_SituationSet instance with only this situation inside, hence
     * see mentioned method's documentation for more details.
     *
     * @param string $token name of token to get transition set for
     *
     * @return SLR_Elements_SituationSet
     *
     * @see SLR_Elements_SituationSet::transition
     */
    public function transition($token)
    {
        $set = new SLR_Elements_SituationSet();
        $set->add($this);
        return $set->transition($token);
    }
}
