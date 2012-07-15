<?php
/**
 * String class.
 *
 * PHP version 5.3
 *
 * @category SLR
 * @package  SLR\Lexer\Matchers
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Lexer\Matchers;

/**
 * String matcher class. Used for matching strings against any static string.
 *
 * @category SLR
 * @package  SLR\Lexer\Matchers
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class String extends AbsMatcher
{
    /**
     * Performs matching operation.
     *
     * @param string $string An arbitrary string to be compared against matcher
     * @param int    $offset Offset of the actual portion of string to be matched
     *
     * @return string|bool Matched string on success, or bool false on failure
     *
     * @see AbsMatcher::match
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