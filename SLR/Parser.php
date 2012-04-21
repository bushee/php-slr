<?php
/**
 * Parser class.
 *
 * PHP version 5.2
 *
 * @category SLR
 * @package  Core
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

/**
 * Main parser class. Use it to create your own parsers.
 *
 * @category SLR
 * @package  Core
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
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
     * @param array    $tokens   input stream of tokens to be parsed
     * @param int      $rowCount optional number of original input's rows, used to
     *                           fill end token's column offset for better error info
     * @param int|bool $expLimit max. amount of expected tokens to be included
     *                           in exception message (in case of parse error)
     *
     * @see SLR_UnexpectedTokenException::__construct for more details on $expLimit
     *
     * @return mixed
     */
    public function parse($tokens, $rowCount = null, $expLimit = 1)
    {
        $endToken = new SLR_Elements_Tokens_End($rowCount);
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
                $expectedTokens = $this->slr->expectedTokens($state);
                if (is_a($next, 'SLR_Elements_Tokens_End')) {
                    $exception = new SLR_UnexpectedEndOfInputException(
                        $next, $expectedTokens
                    );
                } else {
                    $exception = new SLR_UnexpectedTokenException(
                        $next, $expectedTokens, $expLimit
                    );
                }
                return $this->slr->failure($exception);
            }
        }
    }
}