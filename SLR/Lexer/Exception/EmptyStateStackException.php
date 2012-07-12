<?php
/**
 * EmptyStateStackException exception.
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
 * Exception for when lexer rule wanted to go to previous state while state stack
 * was already empty.
 *
 * @category SLR
 * @package  SLR\Lexer\Exception
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class EmptyStateStackException extends AbsLexerException
{
    /**
     * Rule that caused exception.
     *
     * @var array $rule
     */
    protected $rule;

    /**
     * Creates new empty state stack exception.
     *
     * @param int        $row      Row in which the exception occured
     * @param int        $column   Column in which the exception occured
     * @param array      $rule     Rule that caused exception
     * @param int        $code     Exception code
     * @param \Exception $previous Previous exception used for exception chaining
     */
    public function __construct(
        $row, $column, $rule = array(), $code = 0, \Exception $previous = null
    ) {
        parent::__construct(
            $row, $column,
            'Can\'t go to previous state anymore - state stack is empty.',
            $code, $previous
        );
        $this->rule = $rule;
    }

    /**
     * Returns rule that caused the exception.
     *
     * @return array
     */
    public function getRule()
    {
        return $this->rule;
    }
}