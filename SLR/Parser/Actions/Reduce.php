<?php
/**
 * Reduce class.
 *
 * PHP version 5.3
 *
 * @category SLR
 * @package  SLR\Parser\Actions
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Parser\Actions;

use SLR\Parser\Elements\Tokens\Token;
use SLR\Parser\Actions\Exception\ParserCompiledWithErrorsException;

/**
 * Reduce action class. Represents action taken when some grammar rule's conditions
 * have been met and some part of parsing stack should be reduced to a single token.
 *
 * @category SLR
 * @package  SLR\Parser\Actions
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class Reduce extends AbsAction
{
    /**
     * Returns human-readable action type string.
     *
     * @return string
     */
    public function getType()
    {
        return 'reduce';
    }

    /**
     * Returns action prefix, mostly for SLR table printing purpose.
     *
     * @return string
     */
    protected function prefix()
    {
        return 'r';
    }

    /**
     * Performs actual action - tries to consume part of current parsing stack
     * and reduce it to a single token. Stack is truncated accordingly, and newly
     * created token - the one that stack's part was reduced to - is prepended
     * to remaining input stream.
     * After successful execution, ID of state that parser should switch to is
     * returned; it is the same state that parser was in when consuming the first
     * of reduced tokens.
     *
     * @param array &$stack Current parsing stack
     * @param array &$input Remaining input stream
     *
     * @return int
     *
     * @throws ParserCompiledWithErrorsException
     */
    public function execute(array &$stack, array &$input)
    {
        $rule = $this->slr->rule($this->param);
        $right = array();

        $ruleLength = count($rule['right']);
        for ($i = $ruleLength - 1; $i >= 0; -- $i) {
            while (!empty($stack)) {
                $element = array_pop($stack);
                if (is_a($element, 'SLR\Parser\Elements\Tokens\Token')) {
                    /** @var Token $element */
                    if ($element->getType() == $rule['right'][$i]) {
                        array_unshift($right, $element);
                        break;
                    } else {
                        // TODO "less tokens than expected" seems strange to me
                        $actualElementCount = $ruleLength - ($i + 1);
                        throw new ParserCompiledWithErrorsException(
                            'Parsing stack contains less tokens than expected '
                            . "(expected: $ruleLength, was: $actualElementCount)."
                        );
                    }
                }
            }
        }
        if (empty($stack)) {
            throw new ParserCompiledWithErrorsException(
                "Parsing stack is empty after reducing rule {$this->param}."
            );
        } else {
            $reducedToken = $this->_prepareReducedToken(
                $rule['left'], $rule['callback'], $right
            );
            array_unshift($input, $reducedToken);
            return $stack[count($stack) - 1];
        }
    }

    /**
     * Prepares token resulting in tokens reduction.
     *
     * @param string $tokenName Name of result token
     * @param mixed  $callback  Reduction callback
     * @param array  $tokens    List of matched tokens
     *
     * @return Token
     */
    private function _prepareReducedToken($tokenName, $callback, array $tokens)
    {
        $tokenValues = array();
        $state = null;
        $row = null;
        $column = null;

        if (count($tokens) > 0) {
            /* Row and column of result are the same as of first token they are
               available for (because some generic tokens may be created basing on
               empty tokens list, hence not having such information).
               Status makes any sense only if all tokens were matched in the same
               lexer state (or they have no status information, which means they
               are created basing on empty tokens list, too). */
            $stateNotSetYet = true;
            foreach ($tokens as $token) {
                /** @var Token $token */
                $tokenValues[] = $token->getValue();
                if ($row === null) {
                    $row = $token->getRow();
                }
                if ($column === null) {
                    $column = $token->getColumn();
                }
                if ($state === null && $stateNotSetYet) {
                    $state = $token->getState();
                    $stateNotSetYet = false;
                } elseif ($state !== $token->getState()) {
                    $state = null;
                }
            }
        }

        $value = call_user_func($callback, $tokenValues);

        return new Token($tokenName, $value, $state, $row, $column);
    }
}