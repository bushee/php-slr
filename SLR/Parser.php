<?php
/**
 * Parser class.
 *
 * PHP version 5.2.todo
 *
 * @category Main
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */

/**
 * Main parser class. Use it to create your own parsers.
 *
 * @category Main
 * @package  SLR
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  TODO http://todo.org
 * @link     http://bushee.ovh.org
 */
class SLR_Parser
{
    protected $slr;

    public function __construct($slr)
    {
        $this->slr = $slr;
    }

    public function parse($tokens)
    {
        $endToken = $this->slr->getEndToken();
        $tokens[] = $endToken;

        $state = $this->slr->getStartState();
        $stack = array(
            $state
        );

        while (true) {
            $next = $tokens[0];
            $action = $this->slr->actionFor($state, $next->type());

            if ($action) {
                $result = $action->execute($stack, $tokens);

                if ($result === true) {
                    return $this->slr->success($stack[1]->value());
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