<?php
/**
 * Regex class.
 *
 * PHP version 5.2.todo
 *
 * @category Matchers
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */

/**
 * Regex matcher class. Used for matching strings against any regular expression.
 *
 * @category Matchers
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */
class SLR_Matchers_Regex extends SLR_Matchers_AbsMatcher
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
        $result = preg_match(
            $this->pattern, $string, $matches, PREG_OFFSET_CAPTURE, $offset
        );
        if ($result && count($matches) > 0 && $matches[0][1] == $offset) {
            return $matches[0][0];
        } else {
            return false;
        }
    }
}