<?php
abstract class SLR_Matchers_AbsMatcher
{
    protected $pattern;

    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    public static function getMatcher($type, $pattern)
    {
        $className = 'SLR_Matchers_' . ucfirst($type);
        if (class_exists($className)) {
            return new $className($pattern);
        } else {
            throw new Exception("Matcher \"$type\" doesn't exist.");
        }
    }

    public abstract function match(&$string, $offset);
}
