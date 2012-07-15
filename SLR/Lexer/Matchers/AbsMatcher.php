<?php
/**
 * AbsMatcher class.
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

use SLR\Lexer\Matchers\Exception\MatcherNotFoundException;

/**
 * Abstract matcher class. Used for deriving any custom lexer matchers.
 *
 * @category SLR
 * @package  SLR\Lexer\Matchers
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
abstract class AbsMatcher
{
    /**
     * Matcher class prefixes registered in factory.
     * Array key is used only for hashing purposes, so that two equal prefixes
     * are never used.
     *
     * @var array $_registeredPrefixes
     */
    private static $_registeredPrefixes = array(
        'slr\lexer\matchers\\' => 'SLR\Lexer\Matchers\\'
    );

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
     * @param string $pattern Matcher's pattern
     */
    public final function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * Registers new prefix for matcher factory.
     * These prefixes are used to determine final class names for matchers
     * requested via getMatcher() method.
     *
     * @param string $prefix New matcher class prefix to register
     *
     * @return void
     *
     * @see AbsMatcher::getMatcher
     */
    public static function registerPrefix($prefix)
    {
        self::$_registeredPrefixes[strtolower($prefix)] = $prefix;
    }

    /**
     * Returns new instance of matcher of given type.
     * Please note that despite of this method's signature, any surplus parameters
     * will be passed to matcher's constructor as well (keep that in mind while
     * implementing your own custom matchers).
     *
     * @param string $type    Matcher type
     * @param string $pattern Matcher's pattern
     *
     * @return AbsMatcher
     *
     * @throws MatcherNotFoundException If unknown matcher was requested
     *
     * @see AbsMatcher::registerPrefix
     */
    public static function getMatcher($type, $pattern)
    {
        foreach (self::$_registeredPrefixes as $prefix) {
            $className = $prefix . ucfirst($type);
            if (class_exists($className)) {
                $instance = new $className($pattern);
                if (is_a($instance, 'SLR\Lexer\Matchers\AbsMatcher')) {
                    /** @var AbsMatcher $instance */
                    $instance->init(array_slice(func_get_args(), 2));
                    return $instance;
                }
            }
        }
        throw new MatcherNotFoundException($type);
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
     * @param string $string An arbitrary string to be compared against matcher
     * @param int    $offset Offset of the actual portion of string to be matched
     *
     * @return string|bool Matched string on success, or bool false on failure
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
