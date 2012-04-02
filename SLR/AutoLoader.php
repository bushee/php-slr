<?php
/**
 * Autoloader class.
 *
 * PHP version 5.2.todo
 *
 * @category Core
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */

/**
 * Autoloader class. Used to automagically load demanded classes.
 *
 * @category Core
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */
class SLR_AutoLoader
{
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
     * @param string $classname demanded class' name
     *
     * @return void
     */
    public function load($classname)
    {
        $classParts = explode('_', $classname);
        if ($classParts[0] === 'SLR') {
            array_shift($classParts);
            $filepath = implode(DIRECTORY_SEPARATOR, $classParts);
            $filename = $this->_basedir . $filepath . '.php';
            if (file_exists($filename)) {
                include_once $filename;
            }
        }
    }
}