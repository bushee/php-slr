<?php
/**
 * Autoloader class.
 *
 * PHP version 5.3
 *
 * @category SLR
 * @package  SLR\Utils
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Utils;

/**
 * Autoloader class. Used to automagically load demanded classes.
 *
 * @category SLR
 * @package  Core
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class AutoLoader
{
    /**
     * Top-level namespace supported by this autoloader.
     *
     * @const string SUPPORTED_NAMESPACE
     */
    const SUPPORTED_NAMESPACE = 'SLR\\';

    /**
     * Path to SLR base directory.
     *
     * @var string $basedir
     */
    private $_basedir;

    /**
     * Autoloader constructor.
     *
     * @param string $basedir SLR base directory path
     */
    public function __construct($basedir)
    {
        $this->_basedir = $basedir . DIRECTORY_SEPARATOR;
    }

    /**
     * Initialies autoloader.
     *
     * @return void
     */
    public function initialize()
    {
        // ensure any already defined __autoload() is contained in spl_autoload stack
        $autoloadStack = spl_autoload_functions();
        if (function_exists('__autoload')
            && (!is_array($autoloadStack)
            || empty($autoloadStack)
            || in_array('__autoload', $autoloadStack))
        ) {
            spl_autoload_register('__autoload');
        }

        spl_autoload_register(array($this, 'load'));
    }

    /**
     * Tries to load demanded class.
     *
     * @param string $classname Demanded class' name
     *
     * @return bool
     */
    public function load($classname)
    {
        if (strpos($classname, self::SUPPORTED_NAMESPACE) === 0) {
            $filename = $this->_basedir
                . str_replace('\\', DIRECTORY_SEPARATOR, substr($classname, 4))
                . '.php';
            if (file_exists($filename)) {
                include_once $filename;
                return true;
            }
        }
        
        return false;
    }
}