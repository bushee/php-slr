<?php
/**
 * SituationSet class.
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

/**
 * Situation set class.
 * Class implements Iterator interface for convenient iteration purpose. It will
 * iterate over all situations, in order of their addition.
 * Class implements ArrayAccess interface as well, for provide easy access to stored
 * situations by their keys.
 *
 * @category SLR
 * @package  SLR\Parser\Elements
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class SituationSet implements \ArrayAccess, \Iterator
{
    /**
     * Situations stored in set.
     *
     * @var array $set
     */
    protected $set;
    /**
     * Unique key representing data stored in set. May be used to distinquish sets.
     *
     * @var string $key
     */
    protected $key;

    /**
     * Creates new empty situation set object.
     */
    public function __construct()
    {
        $this->set = array();
        $this->invalidateKey();
    }

    /**
     * Checks whether situation with given key belongs to this set, for array access
     * purposes.
     *
     * @param string $key Key of situation to be checked
     *
     * @return bool
     *
     * @see \ArrayAccess::offsetExists
     */
    public function offsetExists($key)
    {
        return isset($this->set[$key]);
    }

    /**
     * Returns situation of given key, for array access purposes.
     *
     * @param string $key Key of situation to be retrieved
     *
     * @return Situation
     *
     * @see \ArrayAccess::offsetGet
     */
    public function offsetGet($key)
    {
        return $this->set[$key];
    }

    /**
     * Adds new situation to set. Due to set's specific behaviour, given $offset is
     * never used, and actual key of given situation is used instead.
     * In fact, this method is only a proxy for SituationSet::add(), implemented only
     * to comply with ArrayAccess interface. The only difference is that given
     * situation is always added as a result.
     *
     * @param string    $offset    Never used; compliance with interface
     * @param Situation $situation Situation to be added to set
     *
     * @return Situation
     *
     * @see \ArrayAccess::offsetSet
     * @see SituationSet::add
     */
    public function offsetSet($offset, $situation)
    {
        $this->add($situation);
        return $situation;
    }

    /**
     * Removes situation from set by its key, for array access purposes.
     *
     * @param string $key Key of situation to be removed
     *
     * @return void
     *
     * @see \ArrayAccess::offsetUnset
     */
    public function offsetUnset($key)
    {
        unset($this->set[$key]);
    }

    /**
     * Tries to add new situation to set. If situation is already known, no actual
     * action is taken and boolean false is returned; boolean true is returned
     * otherwise.
     *
     * @param Situation $situation Situation to be included in set
     *
     * @return bool
     */
    public function add(Situation $situation)
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

    /**
     * Returns current element for iteration purpose.
     *
     * @return Situation
     *
     * @see \Iterator::current
     */
    public function current()
    {
        return current($this->set);
    }

    /**
     * Returns current element's key for iteration purpose.
     *
     * @return string
     *
     * @see \Iterator::key
     */
    public function key()
    {
        return key($this->set);
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
        next($this->set);
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
        reset($this->set);
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
        return key($this->set) !== null;
    }

    /**
     * Returns closure of this situation set.
     * Closure is extension of a set that contains also all situations available one
     * step further from any situation in given set.
     *
     * @return SituationSet
     */
    public function closure()
    {
        $situations = $this->set;
        $set = new SituationSet();

        while (!empty($situations)) {
            /** @var Situation $situation */
            $situation = array_shift($situations);
            if ($set->add($situation)) {
                $slr = $situation->slr;
                foreach ($slr->rulesOf($situation->next()) as $id) {
                    $situations[] = new Situation($slr, $id, 0);
                }
            }
        }

        return $set;
    }

    /**
     * Returns transition set of this situation set.
     * Transition set is closure of a set that consists of all situations available
     * one step further form this set after accepting given token.
     *
     * @param string $token Name of token to get transition set for
     *
     * @return SituationSet
     */
    public function transition($token)
    {
        $set = new SituationSet();

        /** @var Situation $situation */
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

    /**
     * Returns list of tokens that could be accepted from any situation in this
     * situation set.
     *
     * @return array
     */
    public function nextTokens()
    {
        $next = array();

        /** @var Situation $situation */
        foreach ($this->set as $situation) {
            $token = $situation->next();
            if (isset($token)) {
                $next[$token] = $token;
            }
        }

        return $next;
    }

    /**
     * Compares this situation set to another one, checking whether they are same.
     *
     * @param SituationSet $set Another set to compare this one to
     *
     * @return bool
     */
    public function equals(SituationSet $set)
    {
        return $this->getKey() == $set->getKey();
    }

    /**
     * Returns an unique key that could distinguish one situation set from any other.
     * Once calculated, key is cached until it is invalidated with call to
     * SituationSet::invalidateKey method.
     *
     * @return string
     *
     * @see SituationSet::invalidateKey
     */
    public function getKey()
    {
        if (!isset($this->key)) {
            $keys = array();
            /** @var Situation $situation */
            foreach ($this->set as $situation) {
                $key = $situation->getKey();
                $keys[$key] = $key;
            }
            sort($keys);
            $this->key = implode('|', $keys);
        }

        return $this->key;
    }

    /**
     * Invalidates cached key, so that with next call to SituationSet::getKey it will
     * be calculated from scratch.
     *
     * @return void
     *
     * @see SituationSet::getKey
     */
    public function invalidateKey()
    {
        $this->key = null;
    }

    /**
     * Returns human-readable situation set representation. It simply contains of
     * list of all situations contained in set.
     *
     * @return string
     */
    public function __toString()
    {
        $ret = '';

        foreach ($this->set as $situation) {
            $ret .= "$situation\n";
        }

        return $ret;
    }
}
