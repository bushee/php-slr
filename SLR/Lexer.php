<?php
/**
 * Lexer class.
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
 * Main lexer class. Provides stand-alone lexer functionality, however you may use
 * it to derive any custom lexers.
 *
 * @category Core
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */
class SLR_Lexer
{
    /**
     * List of lexer rules.
     *
     * @var array $rules
     */
    protected $rules;
    /**
     * Should unrecognized token be used for any tokens that couldn't have been
     * matched against any matchers?
     *
     * @var bool $useUnrecognizedToken
     */
    protected $useUnrecognizedToken;
    /**
     * Row of input the lexer's head is currently in.
     *
     * @var int $row
     */
    protected $row;
    /**
     * Column of input the lexer's head is currently in.
     *
     * @var int $column
     */
    protected $column;

    /**
     * Prepares lexer.
     *
     * @param array $config               lexer configuration
     * @param bool  $useUnrecognizedToken should unrecognized token be used for any
     *                                    tokens that couldn't have been matched
     *                                    against any matchers?
     */
    public function __construct($config, $useUnrecognizedToken = true)
    {
        $this->useUnrecognizedToken = $useUnrecognizedToken;

        $this->rules = array('all' => array());
        foreach ($config as $state => $rules) {
            foreach ($rules as $rule) {
                if (is_callable($rule[2])) {
                    $callback = $rule[2];
                } else {
                    $callback = array($this, 'defaultCallback');
                }

                $this->rules[$state][] = array(
                    'matcher' => SLR_Matchers_AbsMatcher
                        ::getMatcher($rule[0], $rule[1]),
                    'callback' => $callback,
                    'stateSwitch' => $rule[3]
                );
            }
        }
    }

    /**
     * Default callback used for any rules for which custom callbacks weren't
     * specified.
     *
     * @return null
     */
    public function defaultCallback()
    {
        return null;
    }

    /**
     * Performs lexing (tokenization) of given input string.
     *
     * @param string $string input to be tokenized
     *
     * @return array
     */
    public function lex($string)
    {
        $offset = 0;
        $length = strlen($string);
        $currentState = 'initial';
        $stateStack = array($currentState);
        $tokens = array();
        // TODO: counting rows and columns and adding them to tokens
        $this->row = 0;
        $this->column = 0;

        $unrecognized = null;

        while ($offset < $length) {
            $rules = array_merge($this->rules[$currentState], $this->rules['all']);
            $matched = false;

            foreach ($rules as $rule) {
                $matched = $rule['matcher']->match($string, $offset);
                if ($matched !== false) {
                    $this->handleUnrecognized($unrecognized, &$tokens);
                    $unrecognized = null;

                    $offset += strlen($matched);
                    $type = call_user_func($rule['callback'], &$matched);
                    $tokens[] = new SLR_Elements_Tokens_Token(
                        $type, $matched, $currentState
                    );

                    if ($rule['stateSwitch']) {
                        if ($rule['stateSwitch'] == 'previous') {
                            if (count($stateStack) == 1) {
                                throw new SLR_EmptyStateStackException(
                                    $this->row, $this->column, $rule
                                );
                            }
                            array_pop($stateStack);
                            $currentState = $stateStack[count($stateStack) - 1];
                        } else {
                            $currentState = $rule['stateSwitch'];
                            $stateStack[] = $currentState;
                        }
                    }

                    $matched = true;

                    break;
                }
            }

            if (!$matched) {
                $unrecognized .= $string[$offset];
                ++ $offset;
            }
        }
        $this->handleUnrecognized($unrecognized, &$tokens);

        return $tokens;
    }

    /**
     * Handles unrecognized string:
     * - if string is empty, does nothing, otherwise:
     *  * adds unrecognized token to output stream, if lexer has such setting enabled
     *  * throws an exception otherwise
     * Output stream of tokens should always be passed by reference, since this
     * method is allowed to modify it.
     *
     * @param string $unrecognized the unrecognized string that couldn't have been
     *                             matched against any lexer rule
     * @param array  $tokens       the output stream of tokens so far
     *
     * @return void
     */
    protected function handleUnrecognized($unrecognized, $tokens)
    {
        if (isset($unrecognized)) {
            if ($this->useUnrecognizedToken) {
                $tokens[] = new SLR_Elements_Tokens_Unrecognized(
                    $unrecognized, $currentState
                );
            } else {
                throw new SLR_UnrecognizedTokenException(
                    $this->row, $this->column, $unrecognized
                );
            }
        }
    }
}
