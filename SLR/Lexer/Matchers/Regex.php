<?php
/**
 * Regex class.
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
 * Regex matcher class. Used for matching strings against any regular expression.
 *
 * @category SLR
 * @package  SLR\Lexer\Matchers
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class Regex extends AbsMatcher
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