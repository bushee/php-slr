<?php
/**
 * Parser class.
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
 * Main parser class. Use it to create your own parsers.
 *
 * @category Core
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */
class SLR_Parser
{
    /**
     * SLR instance to use for parsing.
     *
     * @var SLR_SLR $slr
     */
    protected $slr;

    /**
     * Creates parser instance.
     *
     * @param SLR_SLR $slr SLR instance to use for parsing
     */
    public function __construct($slr)
    {
        $this->slr = $slr;
    }

    /**
     * Performs parsing.
     * Depending on parse status, will call SLR's success() or failure() method,
     * returning value which it yields. Action taken upon failure is most probable
     * to throw an exception, however it is not said it is necessary; it's up to
     * programmer to take it into account and handle it properly.
     *
     * Please note that parser performs parsing itself, and string tokenization is
     * a matter of proper lexer.
     *
     * @param array $tokens input stream of tokens to be parsed
     *
     * @return mixed
     */
    public function parse($tokens)
    {
        $endToken = new SLR_Elements_Tokens_End();
        $tokens[] = $endToken;

        $state = $this->slr->getStartState();
        $stack = array(
            $state
        );

        while (true) {
            $next = $tokens[0];
            $action = $this->slr->actionFor($state, $next->getType());

            if ($action) {
                $result = $action->execute($stack, $tokens);

                if ($result === true) {
                    return $this->slr->success($stack[1]->getValue());
                } else {
                    $state = $result;
                }
            } else {
                if ($next == $endToken) {
                    // TODO this exception should carry list of expected tokens
                    $exception = new Exception('unfinished');
                } else {
                    // TODO this exception should carry list of expected tokens
                    $exception = new Exception("Can't consume $next");
                }
                return $this->slr->failure($exception);
            }
        }
    }
}