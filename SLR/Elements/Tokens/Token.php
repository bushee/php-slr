<?php
/**
 * Token class.
 *
 * PHP version 5.2.todo
 *
 * @category Tokens
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */

/**
 * Token class. Used to represent any given token, as well as a base for custom token
 * classes.
 *
 * @category Tokens
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */
class SLR_Elements_Tokens_Token
{
    /**
     * Token type.
     *
     * @var string $type
     */
    protected $type;
    /**
     * Token value.
     *
     * @var mixed $value
     */
    protected $value;
    /**
     * Lexer's state that token was captured in.
     *
     * @var string $state
     */
    protected $state;
    /**
     * Input row that token was captured in.
     *
     * @var string $row
     */
    protected $row;
    /**
     * Input column that token was captured in.
     *
     * @var string $column
     */
    protected $column;

    /**
     * Creates new token.
     *
     * @param string $type   token type
     * @param mixed  $value  token value
     * @param string $state  lexer's state that token was captured in
     * @param int    $row    input row that token was captured in
     * @param int    $column input column that token was captured in
     */
    public function __construct(
        $type, $value = null, $state = null, $row = null, $column = null
    ) {
        $this->type = $type;
        $this->value = $value;
        $this->state = $state;
        $this->row = $row;
        $this->column = $column;
    }

    /**
     * Returns token's type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns token's value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns lexer's state that token was captured in.
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Returns input row that token was captured in.
     *
     * @return int
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * Returns input column that token was captured in.
     *
     * @return int
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Returns token's human readable string representation.
     *
     * @return string
     */
    public function __toString()
    {
        $s = $this->type;
        $additional = array();
        if (isset($this->value)) {
            $additional[] = '"' . $this->value . '"';
        }
        if (isset($this->state)) {
            $additional[] = '@' . $this->state;
        }
        if (isset($this->row)) {
            $additional[] = 'row: ' . $this->row;
        }
        if (isset($this->column)) {
            $additional[] = 'column: ' . $this->column;
        }
        if (!empty($additional)) {
            $s .= ' (' . implode(' ', $additional) . ')';
        }
        return $s;
    }
}