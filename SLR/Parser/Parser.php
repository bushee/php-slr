<?php
/**
 * Parser class.
 *
 * PHP version 5.2
 *
 * @category SLR
 * @package  SLR\Parser
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Parser;

use SLR\Parser\Elements\Tokens\Token;

/**
 * Main parser class. Use it to create your own parsers.
 *
 * @category SLR
 * @package  SLR\Parser
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class Parser
{
    /**
     * SLR instance to use for parsing.
     *
     * @var SLRTable $slr
     */
    protected $slr;

    /**
     * Creates parser instance.
     *
     * @param SLRTable $slr SLR instance to use for parsing
     */
    public function __construct(SLRTable $slr)
    {
        $this->slr = $slr;
    }

    /**
     * Performs parsing.
     * Depending on parse status, will call SLR tables' success() or failure()
     * method, returning value which it yields. Action taken upon failure is most
     * probable to throw an exception, however it is not said it is necessary; it's
     * up to programmer to take it into account and handle it properly.
     *
     * Please note that parser performs parsing itself, and string tokenization is
     * a matter of proper lexer.
     *
     * @param array    $tokens   Input stream of tokens to be parsed
     * @param int      $rowCount Optional number of original input's rows, used to
     *                           fill end token's column offset for better error info
     * @param int|bool $expLimit Max. amount of expected tokens to be included
     *                           in exception message (in case of parse error)
     *
     * @see Exception\UnexpectedTokenException for more details on $expLimit
     *
     * @return mixed
     */
    public function parse(array $tokens, $rowCount = null, $expLimit = 1)
    {
        $endToken = new Elements\Tokens\End($rowCount);
        $tokens[] = $endToken;

        $state = $this->slr->getStartState();
        $stack = array(
            $state
        );

        $returnValue = null;

        while (true) {
            /** @var Token $next */
            $next = $tokens[0];
            $action = $this->slr->actionFor($state, $next->getType());

            if ($action) {
                $result = $action->execute($stack, $tokens);

                if ($result === true) {
                    $returnValue = $this->slr->success($stack[1]->getValue());
                    break;
                } else {
                    $state = $result;
                }
            } else {
                $expectedTokens = $this->slr->expectedTokens($state);
                if (is_a($next, 'SLR\Parser\Elements\Tokens\End')) {
                    $exception = new Exception\UnexpectedEndOfInputException(
                        $next, $expectedTokens
                    );
                } else {
                    $exception = new Exception\UnexpectedTokenException(
                        $next, $expectedTokens, $expLimit
                    );
                }
                $returnValue = $this->slr->failure($exception);
                break;
            }
        }
        return $returnValue;
    }
}