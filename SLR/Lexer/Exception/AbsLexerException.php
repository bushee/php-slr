<?php
/**
 * AbsLexerException exception.
 *
 * PHP version 5.2
 *
 * @category SLR
 * @package  SLR\Lexer\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Lexer\Exception;

/**
 * Abstract lexer exception for deriving any lexer-based exceptions.
 *
 * @category SLR
 * @package  SLR\Lexer\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class AbsLexerException extends \Exception
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
     * @param int        $row      Row in which the exception occured
     * @param int        $column   Column in which the exception occured
     * @param string     $message  Exception message to throw
     * @param int        $code     Exception code
     * @param \Exception $previous Previous exception used for exception chaining
     */
    public function __construct(
        $row, $column, $message = '', $code = 0, \Exception $previous = null
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