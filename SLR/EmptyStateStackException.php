<?php
/**
 * EmptyStateStackException exception.
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
 * Exception for when lexer rule wanted to go to previous state while state stack
 * was already empty.
 *
 * @category   SLR
 * @package    Core
 * @subpackage Exceptions
 * @author     Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       http://bushee.ovh.org
 */
class SLR_EmptyStateStackException extends SLR_AbsLexerException
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
     * @param int       $row      row in which the exception occured
     * @param int       $column   column in which the exception occured
     * @param array     $rule     rule that caused exception
     * @param int       $code     the exception code
     * @param Exception $previous the previous exception used for the exception
     *                            chaining
     */
    public function __construct(
        $row, $column, $rule = array(), $code = 0, $previous = null
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