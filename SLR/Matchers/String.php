<?php
/**
 * String class.
 *
 * PHP version 5.2
 *
 * @category SLR
 * @package  Matchers
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

/**
 * String matcher class. Used for matching strings against any static string.
 *
 * @category SLR
 * @package  Matchers
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class SLR_Matchers_String extends SLR_Matchers_AbsMatcher
{
    /**
     * Performs matching operation.
     *
     * @param string $string an arbitrary string to be compared against matcher
     * @param int    $offset offset of the actual portion of string to be matched
     *
     * @return mixed matched string on success, or bool false on failure
     *
     * @see SLR_Matchers_AbsMatcher::match
     */
    public function match($string, $offset)
    {
        if (substr($string, $offset, strlen($this->pattern)) == $this->pattern) {
            return $this->pattern;
        } else {
            return false;
        }
    }
}