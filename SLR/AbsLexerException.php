<?php
/**
 * AbsLexerException exception.
 *
 * PHP version 5.2.todo
 *
 * @category   SLR
 * @package    Core
 * @subpackage Exceptions
 * @author     Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       http://bushee.ovh.org
 */

/**
 * Abstract lexer exception for deriving any lexer-based exceptions.
 *
 * @category   SLR
 * @package    Core
 * @subpackage Exceptions
 * @author     Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       http://bushee.ovh.org
 */
class SLR_AbsLexerException extends Exception
{
    /**
     * Row in which the exception occured.
     *
     * @var int $row
     */
    protected $row;
    /**
     * Column in which the exception occured.
     *
     * @var int $column
     */
    protected $column;

    /**
     * Creates new lexer exception.
     *
     * @param int       $row      row in which the exception occured
     * @param int       $column   column in which the exception occured
     * @param string    $message  the exception message to throw
     * @param int       $code     the exception code
     * @param Exception $previous the previous exception used for the exception
     *                            chaining
     */
    public function __construct(
        $row, $column, $message = '', $code = 0, $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->row = $row;
        $this->column = $column;
    }

    /**
     * Returns row in which the exception occured.
     *
     * @return int
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * Returns column in which the exception occured.
     *
     * @return int
     */
    public function getColumn()
    {
        return $this->column;
    }
}