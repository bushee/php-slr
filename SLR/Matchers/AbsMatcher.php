<?php
/**
 * AbsMatcher class.
 *
 * PHP version 5.2.todo
 *
 * @category SLR
 * @package  Matchers
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

/**
 * Abstract matcher class. Used for deriving any custom lexer matchers.
 *
 * @category SLR
 * @package  Matchers
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
abstract class SLR_Matchers_AbsMatcher
{
    /**
     * Matcher's pattern. It's real meaning is abstract, and depends purely on
     * matcher instance; however, it is stored here for convenience.
     *
     * @var string $pattern
     */
    protected $pattern;

    /**
     * Creates a matcher instance.
     *
     * @param string $pattern matcher's pattern
     */
    public final function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * Returns new instance of matcher of given type.
     * Please note that despite of this method's signature, any surplus parameters
     * will be passed to matcher's constructor as well (keep that in mind while
     * implementing your own custom matchers).
     *
     * @param string $type    matcher type
     * @param string $pattern matcher's pattern
     *
     * @return SLR_Matchers_AbsMatcher
     */
    public static function getMatcher($type, $pattern)
    {
        // TODO add custom prefixes support
        $className = 'SLR_Matchers_' . ucfirst($type);
        if (class_exists($className)) {
            $instance = new $className($pattern);
            $instance->init(array_slice(func_get_args(), 2));
            return $instance;
        } else {
            throw new SLR_Matchers_MatcherNotFoundException($type);
        }
    }

    /**
     * Performs matching operation.
     * Implementation of this method is responsible for matching incoming string
     * agains matcher's pattern or any custom rules.
     * Please note that input string may contain any amount of extra data both of
     * left and right side of the string that should be tried to be matched; this
     * method is expected to try to fit only some portion of string indicated by
     * given starting point (offset), leaving the rest intact.
     *
     * @param string $string an arbitrary string to be compared against matcher
     * @param int    $offset offset of the actual portion of string to be matched
     *
     * @return mixed matched string on success, or bool false on failure
     */
    public abstract function match($string, $offset);

    /**
     * Performs matcher initialisation.
     * This method may take any arbitrary-length list of parameters, as matcher
     * factory will handle and pass them properly.
     * Custom implementation is not necessary, and default implementation simply
     * does nothing, ignoring incoming parameters.
     *
     * @return void
     */
    public function init()
    {
    }
}
