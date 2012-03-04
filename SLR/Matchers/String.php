<?php
class SLR_Matchers_String extends SLR_Matchers_AbsMatcher
{
    public function match(&$string, $offset)
    {
        if (substr($string, $offset, strlen($this->pattern)) == $this->pattern) {
            return $this->pattern;
        } else {
            return false;
        }
    }
}