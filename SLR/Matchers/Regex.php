<?php
class SLR_Matchers_Regex extends SLR_Matchers_AbsMatcher
{
    public function match(&$string, $offset)
    {
        preg_match($this->pattern, $string, $matches, PREG_OFFSET_CAPTURE, $offset);
        if (count($matches) > 0 && $matches[0][1] == $offset) {
            return $matches[0][0];
        } else {
            return false;
        }
    }
}