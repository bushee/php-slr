<?php
/**
 * Lexer class.
 *
 * PHP version 5.3
 *
 * @category SLR
 * @package  SLR\Lexer
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Lexer;

use SLR\Parser\Elements\Tokens\Token;
use SLR\Lexer\Exception\UnrecognizedTokenException;
use SLR\Lexer\Exception\EmptyStateStackException;
use SLR\Parser\Elements\Tokens\Unrecognized;
use SLR\Lexer\Matchers\AbsMatcher;

/**
 * Main lexer class. Provides stand-alone lexer functionality, however you may use
 * it to derive any custom lexers.
 *
 * @category SLR
 * @package  SLR\Lexer
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class Lexer
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
     * Row of input the lexer's caret is currently in.
     *
     * @var int $caretRow
     */
    protected $caretRow;
    /**
     * Column of input the lexer's caret is currently in.
     *
     * @var int $caretColumn
     */
    protected $caretColumn;

    /**
     * Prepares lexer.
     *
     * @param array $config               Lexer configuration
     * @param bool  $useUnrecognizedToken Should unrecognized token be used for any
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
                    'matcher' => AbsMatcher::getMatcher($rule[0], $rule[1]),
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
     * @param string $string Input to be tokenized
     *
     * @return array
     *
     * @throws EmptyStateStackException When state stack was empty while return to
     *                                  previous state was requested
     */
    public function lex($string)
    {
        $offset = 0;
        $length = strlen($string);
        $currentState = 'initial';
        $stateStack = array($currentState);
        $tokens = array();
        $this->caretRow = 1;
        $this->caretColumn = 1;

        $unrecognized = null;

        while ($offset < $length) {
            $rules = array_merge($this->rules[$currentState], $this->rules['all']);
            $matched = false;

            foreach ($rules as $rule) {
                $matched = $rule['matcher']->match($string, $offset);
                if ($matched !== false) {
                    $this->handleUnrecognized($unrecognized, $currentState, $tokens);
                    $unrecognized = null;

                    $offset += strlen($matched);
                    /* dirty hack - called call_user_func_array instead of
                       call_user_func just to avoid E_STRICT warning  due to
                       call-time passing variable by reference */
                    $type = call_user_func_array(
                        $rule['callback'], array(&$matched)
                    );
                    $tokens[] = new Token(
                        $type, $matched, $currentState,
                        $this->caretRow, $this->caretColumn
                    );

                    if ($rule['stateSwitch']) {
                        if ($rule['stateSwitch'] == 'previous') {
                            if (count($stateStack) == 1) {
                                throw new EmptyStateStackException(
                                    $this->caretRow, $this->caretColumn, $rule
                                );
                            }
                            array_pop($stateStack);
                            $currentState = $stateStack[count($stateStack) - 1];
                        } else {
                            $currentState = $rule['stateSwitch'];
                            $stateStack[] = $currentState;
                        }
                    }

                    $this->updateCaret($matched);
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                $unrecognized .= $string[$offset];
                ++ $offset;
            }
        }
        $this->handleUnrecognized($unrecognized, $currentState, $tokens);

        return $tokens;
    }

    /**
     * Updates lexer's caret position, basing on newly consumed fragment of input.
     *
     * @param string $text Newly matched text to be used to update caret position
     *
     * @return void
     */
    protected function updateCaret($text)
    {
        $possibleEols = array("\r\n" => 2, "\n\r" => 2, "\r" => 1, "\n" => 1);

        $offset = 0;
        do {
            $found = false;
            foreach ($possibleEols as $eol => $length) {
                $pos = mb_strpos($text, $eol, $offset);
                if ($pos !== false) {
                    $found = true;
                    $offset = $pos + $length;
                    ++ $this->caretRow;
                    $this->caretColumn = 1;
                }
            }
        } while ($found);
        $this->caretColumn += mb_strlen(mb_substr($text, $offset));
    }

    /**
     * Returns lexer's caret position.
     * Return result has two indices: 'row' and 'column'.
     *
     * @return array
     */
    public function getCaretPosition()
    {
        return array(
            'row' => $this->caretRow,
            'column' => $this->caretColumn
        );
    }

    /**
     * Handles unrecognized string:
     * - if string is empty, does nothing, otherwise:
     *  * adds unrecognized token to output stream, if lexer has such setting enabled
     *  * throws an exception otherwise
     * Output stream of tokens should always be passed by reference, since this
     * method is allowed to modify it.
     *
     * @param string $unrecognized Unrecognized string that couldn't have been
     *                             matched against any lexer rule
     * @param string $currentState Current lexer state
     * @param array  &$tokens      Output stream of tokens so far
     *
     * @return void
     *
     * @throws UnrecognizedTokenException When unrecognized token was detected for
     *                                    lexer that should not register it anyway
     */
    protected function handleUnrecognized($unrecognized, $currentState, &$tokens)
    {
        if (isset($unrecognized)) {
            if ($this->useUnrecognizedToken) {
                $tokens[] = new Unrecognized(
                    $unrecognized, $currentState, $this->caretRow, $this->caretColumn
                );
                $this->updateCaret($unrecognized);
            } else {
                $caretRow = $this->caretRow;
                $caretColumn = $this->caretColumn;
                $this->updateCaret($unrecognized);
                throw new UnrecognizedTokenException(
                    $caretRow, $caretColumn, $unrecognized
                );
            }
        }
    }
}
