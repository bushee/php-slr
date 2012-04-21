<?php
/**
 * Reduce class.
 *
 * PHP version 5.2
 *
 * @category SLR
 * @package  Actions
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

/**
 * Reduce action class. Represents action taken when some grammar rule's conditions
 * have been met and some part of parsing stack should be reduced to a single token.
 *
 * @category SLR
 * @package  Actions
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class SLR_Actions_Reduce extends SLR_Actions_AbsAction
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
     * @param array &$stack current parsing stack
     * @param array &$input remaining input stream
     *
     * @return int
     */
    public function execute(&$stack, &$input)
    {
        $rule = $this->slr->rule($this->param);
        $right = array();

        $ruleLength = count($rule['right']);
        for ($i = $ruleLength - 1; $i >= 0; -- $i) {
            while (!empty($stack)) {
                $element = array_pop($stack);
                if (is_a($element, 'SLR_Elements_Tokens_Token')) {
                    if ($element->getType() == $rule['right'][$i]) {
                        array_unshift($right, $element->getValue());
                        break;
                    } else {
                        $actualElementCount = $ruleLength - ($i + 1);
                        throw new SLR_Actions_ParserCompiledWithErrorsException(
                            'Parsing stack contains less tokens than expected '
                            . "(expected: $ruleLength, was: $actualElementCount)."
                        );
                    }
                }
            }
        }
        if (empty($stack)) {
            throw new SLR_Actions_ParserCompiledWithErrorsException(
                "Parsing stack is empty after reducing rule {$this->param}."
            );
        } else {
            $value = call_user_func($rule['callback'], $right);
            array_unshift(
                $input, new SLR_Elements_Tokens_Token($rule['left'], $value)
            );
            return $stack[count($stack) - 1];
        }
    }
}