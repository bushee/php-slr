<?php
/**
 * SLRTable class.
 *
 * PHP version 5.3
 *
 * @category SLR
 * @package  SLR\Parser
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */

namespace SLR\Parser;

use SLR\Parser\Elements\Tokens\End;
use SLR\Parser\Elements\Tokens\Epsilon;
use SLR\Parser\Actions\Accept;
use SLR\Parser\Actions\Reduce;
use SLR\Parser\Actions\Shift;
use SLR\Parser\Actions\Transition;
use SLR\Parser\Elements\TransitionSet;
use SLR\Utils\TablePrinter;
use SLR\Parser\Elements\State;
use SLR\Parser\Elements\Situation;

/**
 * SLRTable class. Provides parsing information.
 *
 * @category SLR
 * @package  SLR\Parser
 * @author   Krzysztof "Bushee" Nowaczyk <bushee01@gmail.com>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     http://bushee.ovh.org
 */
class SLRTable
{
    /**
     * Name for meta-nonterminal that whole input stream should be reducable to.
     *
     * @const string START_META_NONTERMINAL_NAME
     */
    const START_META_NONTERMINAL_NAME = '<start>';

    /**
     * Grammar's start token's name.
     *
     * @var string $startToken
     */
    protected $startToken;
    /**
     * Lists of rules for each non-terminal token.
     *
     * @var array $rulesByLefts
     */
    protected $rulesByLefts;
    /**
     * Lists of rules in which a specific token is used on right side.
     *
     * @var array $rulesByRights
     */
    protected $rulesByRights;
    /**
     * List of rules ordered by their IDs.
     *
     * @var array $rulesOrdered
     */
    protected $rulesOrdered;
    /**
     * Canonical situation set for grammar.
     *
     * @var TransitionSet $canonicalSituationSetFamily
     */
    protected $canonicalSituationSetFamily;
    /**
     * List of terminal tokens' names.
     *
     * @var array $terminalTokens
     */
    protected $terminalTokens;
    /**
     * List of non-terminal tokens' names.
     *
     * @var array $nonterminalTokens
     */
    protected $nonterminalTokens;

    /**
     * FIRST set.
     *
     * @var array $first
     */
    protected $first;
    /**
     * FOLLOW set.
     *
     * @var array $follow
     */
    protected $follow;
    /**
     * SLR table.
     *
     * @var array $slrTable
     */
    protected $slrTable;

    /**
     * Should conflicts be shown in dump?
     *
     * @var bool $showConflicts
     */
    protected $showConflicts;
    /**
     * List of conflicts for any state/token pair.
     *
     * @var array $conflicts
     */
    protected $conflicts;

    /**
     * Callback to be called upon successful parsing.
     *
     * @var mixed $success
     */
    protected $success;
    /**
     * Callback to be called upon failure when parsing.
     *
     * @var mixed $failure
     */
    protected $failure;

    /**
     * Creates new SLR table.
     *
     * @param array $config Parser config
     */
    public function __construct(array $config)
    {
        $this->startToken = $config['start'];
        $this->rulesByLefts = array();
        $this->rulesByRights = array();
        $this->rulesOrdered = array();
        $this->first = array();
        $this->follow = array();
        $this->showConflicts = false;
        $this->conflicts = array();

        $this->addStartRule();
        $tokens = $this->addRules($config['rules']);

        $this->divideTokens($tokens);

        $this->canonicalSituationSetFamily = new TransitionSet($this);

        $this->slrTable = $this->calculateSlrTable();

        $this->addEndCallbacks($config);
    }

    /**
     * Adds special start rule.
     *
     * @return void
     */
    protected function addStartRule()
    {
        $startRule = array(
            'id' => 0,
            'left' => self::START_META_NONTERMINAL_NAME,
            'right' => array($this->startToken),
            'callback' => ''
        );
        $this->rulesByLefts[self::START_META_NONTERMINAL_NAME][0] = $startRule;
        $this->rulesByRights[$this->startToken][0] = $startRule;
        $this->rulesOrdered[0] = $startRule;
    }

    /**
     * Processes and adds all rules given as parameter.
     *
     * @param array $rules Rules to be added
     *
     * @return array List of all tokens used in rules
     */
    protected function addRules(array $rules)
    {
        $tokens = array();

        $id = 1;
        foreach ($rules as $left => $tokenRules) {
            foreach ($tokenRules as $rule) {
                $right = $rule[0];
                if (empty($right)) {
                    // TODO not sure whether epsilon token makes sense at all
                    // $right = array(Epsilon::TOKEN_NAME);
                }

                if (is_callable($rule[1])) {
                    $callback = $rule[1];
                } else {
                    $callback = array(__CLASS__, 'defaultCallback');
                }

                $rule = array(
                    'id' => $id,
                    'left' => $left,
                    'right' => $right,
                    'callback' => $callback
                );

                $this->rulesByLefts[$left][$id] = $rule;
                $this->rulesOrdered[$id] = $rule;

                foreach ($right as $token) {
                    $tokens[$token] = $token;
                    $this->rulesByRights[$token][$id] = $rule;
                }

                ++ $id;
            }
        }

        return $tokens;
    }

    /**
     * Default callback to be used for rules that don't have their own callbacks.
     * By default, value of the first token from right side of rule is returned.
     *
     * @param array $tokens List of tokens matched to rule's right side
     *
     * @return mixed
     */
    public static function defaultCallback($tokens)
    {
        return $tokens[0];
    }

    /**
     * Divides list of tokens between terminal and non-terminal ones.
     * Uses internally stored list of rules to determine which tokens are which,
     * so it is necessary they are filled first.
     *
     * @param array $tokens List of tokens to divide
     *
     * @return void
     */
    protected function divideTokens(array $tokens)
    {
        $this->terminalTokens = array(
        );
        $this->nonterminalTokens = array(
            self::START_META_NONTERMINAL_NAME => self::START_META_NONTERMINAL_NAME
        );

        foreach ($tokens as $token) {
            if (isset($this->rulesByLefts[$token])) {
                $this->nonterminalTokens[$token] = $token;
            } else {
                $this->terminalTokens[$token] = $token;
            }
        }

        $this->terminalTokens[End::TOKEN_NAME] = End::TOKEN_NAME;
        $this->terminalTokens[Epsilon::TOKEN_NAME] = Epsilon::TOKEN_NAME;
    }

    /**
     * Returns FIRST(x) set for given token.
     * This method always returns complete FIRST(x) set for given token, however
     * methods of doing so may differ. First, it tries to get it from cache; if
     * there is no cache hit, it calculates it from scratch, basing on possibly
     * cached values for other x'es. If, on any level of recursion, it finds a token,
     * for which FIRST(x) depends only on tokens already cached (so that it is
     * ensured that their FIRST(x) sets are complete), it caches it too, so that
     * further calls are optimised.
     *
     * @param string $token   Name of token for which to return FIRST(x) value
     * @param array  $visited List of tokens for which FIRST(x) set is being
     *                        calculated on any level of current recursion
     *
     * @return array
     */
    protected function first($token, array $visited = array())
    {
        $visited[$token] = $token;

        if (isset($this->first[$token])) {
            $first = $this->first[$token];
        } elseif ($this->isTerminal($token)) {
            $first = array(
                $token => $token
            );
            $this->first[$token] = $first;
        } else {
            $first = array();
            $save = true;

            foreach ($this->rulesByLefts[$token] as $rule) {
                if (count($rule['right']) == 1
                    && $rule['right'][0] == Epsilon::TOKEN_NAME
                ) {
                    // if X -> epsilon, then epsilon is in first(X)
                    $first[Epsilon::TOKEN_NAME] = Epsilon::TOKEN_NAME;
                } else {
                    $epsilonCounter = 0;
                    foreach ($rule['right'] as $right) {
                        // avoid cycles
                        if (isset($visited[$right])) {
                            if ($right != $token) {
                                // if first(X) for current X depends on any other
                                // tokens, don't save it - it may be incomplete
                                $save = false;
                            }
                            continue;
                        }

                        $rightFirst = $this->first($right, $visited);
                        $epsilon = isset($rightFirst[Epsilon::TOKEN_NAME]);
                        unset($rightFirst[Epsilon::TOKEN_NAME]);

                        // first(Yi)\{epsilon} is in first(X)
                        $first = array_merge($first, $rightFirst);
                        if ($epsilon) {
                            ++ $epsilonCounter;
                        } else {
                            break;
                        }
                    }

                    // if epsilon is in first(Yi) for all i, epsilon is in first(X)
                    if ($epsilonCounter == count($rule['right'])) {
                        $first[Epsilon::TOKEN_NAME] = Epsilon::TOKEN_NAME;
                    }
                }

                if ($save) {
                    $this->first[$token] = $first;
                }
            }
        }

        return $first;
    }

    /**
     * Returns FOLLOW(x) set for given token.
     * This method always returns complete FOLLOW(x) set for given token, however
     * methods of doing so may differ. First, it tries to get it from cache; if
     * there is no cache hit, it calculates it from scratch, basing on possibly
     * cached values for other x'es. If, on any level of recursion, it finds a token,
     * for which FOLLOW(x) depends only on tokens already cached (so that it is
     * ensured that their FOLLOW(x) sets are complete), it caches it too, so that
     * further calls are optimised.
     *
     * @param string $token   Name of token for which to return FOLLOW(x) value
     * @param array  $visited List of tokens for which FOLLOW(x) set is being
     *                        calculated on any level of current recursion
     *
     * @return array
     */
    protected function follow($token, array $visited = array())
    {
        $visited[$token] = $token;

        if (isset($this->follow[$token])) {
            $follow = $this->follow[$token];
        } elseif ($token == self::START_META_NONTERMINAL_NAME) {
            $follow = array(End::TOKEN_NAME);
            $this->follow[self::START_META_NONTERMINAL_NAME] = $follow;
        } else {
            $follow = array();
            $save = true;

            foreach ($this->rulesByRights[$token] as $rule) {
                $length = count($rule['right']);
                for ($i = 0; $i < $length; ++ $i) {
                    $rightToken = $rule['right'][$i];
                    if ($rightToken == $token) {
                        $epsilon = false;
                        if ($i + 1 < $length) {
                            $first = $this->first($rule['right'][$i + 1]);
                            $epsilon = isset($first[Epsilon::TOKEN_NAME]);
                            unset($first[Epsilon::TOKEN_NAME]);
                            $follow = array_merge($follow, $first);
                        }
                        if ($epsilon || !($i + 1 < $length)) {
                            // avoid cycles
                            if (!isset($visited[$rule['left']])) {
                                $follow = array_merge(
                                    $follow, $this->follow($rule['left'], $visited)
                                );
                            } elseif ($token != $rule['left']) {
                                // if follow(X) for current X depends on any other
                                // tokens, don't save it - it may be incomplete
                                $save = false;
                            }
                        }
                    }
                }
            }

            if ($save) {
                $this->follow[$token] = $follow;
            }
        }

        return $follow;
    }

    /**
     * Calculates complete SLR table.
     * This method bases on internally stored canonical situation set family, so it
     * is essential it is calculated prior to calling this method.
     *
     * @return array
     */
    protected function calculateSlrTable()
    {
        $table = array();

        /** @var State $state */
        foreach ($this->canonicalSituationSetFamily as $state) {
            $row = array();

            foreach ($state->getTransitions() as $token => $nextState) {
                if ($this->isTerminal($token)) {
                    $row[$token] = new Shift($this, $nextState);
                } else {
                    $row[$token] = new Transition($this, $nextState);
                }
            }
            /** @var Situation $situation */
            foreach ($state->getSet() as $situation) {
                if (!$situation->hasNext()) {
                    $rule = $situation->getRule();
                    if ($rule['left'] == self::START_META_NONTERMINAL_NAME) {
                        $action = new Accept();
                    } else {
                        $action = new Reduce($this, $rule['id']);
                    }

                    foreach ($this->follow($rule['left']) as $token) {
                        if (isset($row[$token])) {
                            // conflict:
                            // 1. store info about it
                            if (isset($this->conflicts[$state->getId()][$token])) {
                                $this->conflicts[$state->getId()][$token][]
                                    = $action;
                            } else {
                                $this->conflicts[$state->getId()][$token] = array(
                                    $row[$token], $action
                                );
                            }
                            // 2. resolve it by default rule
                            switch ($row[$token]->getType()) {
                            case 'shift':
                                // do nothing - shift remains
                                break;
                            case 'reduce':
                                // select rule with lesser id
                                if ($action->getParam() < $row[$token]->getParam()) {
                                    $row[$token] = $action;
                                }
                                break;
                            }
                        } else {
                            // no conflicts
                            $row[$token] = $action;
                        }
                    }
                }
            }

            $table[$state->getId()] = $row;
        }

        // TODO emit warnings
        return $table;
    }

    /**
     * Adds callbacks for parsing success and failure.
     *
     * @param array $config Parser configuration
     *
     * @return void
     */
    protected function addEndCallbacks(array $config)
    {
        if (isset($config['success']) && is_callable($config['success'])) {
            $this->success = $config['success'];
        } else {
            $this->success = array(__CLASS__, 'defaultSuccess');
        }

        if (isset($config['failure']) && is_callable($config['failure'])) {
            $this->failure = $config['failure'];
        } else {
            $this->failure = array(__CLASS__, 'defaultFailure');
        }
    }

    /**
     * Default success method. It is used upon parsing success, if no parser-specific
     * success method was specified.
     * By default, parser simply yields resulting value (value of final token).
     *
     * @param mixed $value Parsing result
     *
     * @return mixed
     */
    public function defaultSuccess($value)
    {
        return $value;
    }

    /**
     * Default failure method. It is used upon parsing failure, if no parser-specific
     * failure method was specified.
     * By default, parser simply throws exception describing reason of failure.
     *
     * @param \Exception $exception Problem-specific exception instance, ready to
     *                              throw or to be processed some other way
     *
     * @return void
     *
     * @throws \Exception
     */
    public function defaultFailure(\Exception $exception)
    {
        throw $exception;
    }

    /**
     * Returns parsing result upon success.
     * Default implementation simply calls parser-specific success method. However,
     * any derived subclass is free to overload this behaviour, e.g. to simply avoid
     * such proxy.
     *
     * @param mixed $value Parsing result
     *
     * @return mixed
     */
    public function success($value)
    {
        return call_user_func($this->success, $value);
    }

    /**
     * Performs parsing failure handling upon failure.
     * Default implementation simply calls parser-specific failure method. However,
     * any derived subclass is free to overload this behaviour, e.g. to simply avoid
     * such proxy.
     *
     * @param \Exception $exception Problem-specific exception instance, ready to
     *                              throw or to be processed some other way
     *
     * @return mixed
     */
    public function failure(\Exception $exception)
    {
        return call_user_func($this->failure, $exception);
    }

    /**
     * Returns action to be executed on parsing stack for given token in a specific
     * state. If there is no such action, null is returned.
     *
     * @param int    $state State ID
     * @param string $token Token name
     *
     * @return Actions\AbsAction
     */
    public function actionFor($state, $token)
    {
        return $this->slrTable[$state][$token];
    }

    /**
     * Returns list of all tokens expected in given state.
     *
     * @param int $state State ID
     *
     * @return array
     */
    public function expectedTokens($state)
    {
        $expected = array();
        foreach ($this->slrTable[$state] as $token => $dummy) {
            $expected[] = $token;
        }
        return $expected;
    }

    /**
     * Returns rule by its ID.
     *
     * @param int $id Rule ID
     *
     * @return array
     */
    public function rule($id)
    {
        return $this->rulesOrdered[$id];
    }

    /**
     * Returns length (amount of tokens) of rule's right side.
     *
     * @param int $id Rule ID
     *
     * @return int
     */
    public function ruleLength($id)
    {
        return count($this->rulesOrdered[$id]['right']);
    }

    /**
     * Returns list of IDs of rules which have specifed token on their left side.
     *
     * @param string $token Token name
     *
     * @return array
     */
    public function rulesOf($token)
    {
        $ret = array();
        if (isset($token) && isset($this->rulesByLefts[$token])) {
            $ret = array_keys($this->rulesByLefts[$token]);
        }
        return $ret;
    }

    /**
     * Returns whether specific token is terminal.
     *
     * @param string $token Token name
     *
     * @return bool
     */
    public function isTerminal($token)
    {
        return isset($this->terminalTokens[$token]);
    }

    /**
     * Returns whether specific token is non-terminal.
     *
     * @param string $token Token name
     *
     * @return bool
     */
    public function isNonterminal($token)
    {
        return isset($this->nonterminalTokens[$token]);
    }

    /**
     * Returns ID of parser's start state.
     *
     * @return int
     */
    public function getStartState()
    {
        return $this->canonicalSituationSetFamily->getStartState();
    }

    /**
     * Sets flag determining whether conflicts upon SLR table creation should be
     * printed when retrieving its string representation.
     *
     * @param bool $value If true, conflicts will be shown; if false - not
     *
     * @return void
     */
    public function setShowConflicts($value)
    {
        $this->showConflicts = $value;
    }

    /**
     * Returns SLR tables' human-readable representation as string. It consists of
     * whole pretty formatted SLR table, and optionally any conflicts upon its
     * creation (depending on whether showConflicts flag is set).
     *
     * @see SLRTable::setShowConflicts
     *
     * @return string
     */
    public function __toString()
    {
        $table = new TablePrinter();

        $offsetsX = array();
        $offsetsY = array();

        // top header
        $x = 1;
        $table->addBorder(1, TablePrinter::BORDER_HORIZONTAL);
        $table->addBorder($x);
        foreach ($this->terminalTokens as $token) {
            // TODO not sure whether epsilon token makes sense at all
            if ($token != Epsilon::TOKEN_NAME) {
                $table->cell($x, 0, $token);
                $offsetsX[$token] = $x;
                ++ $x;
            }
        }
        $table->addBorder($x);
        foreach ($this->nonterminalTokens as $token) {
            if ($token != self::START_META_NONTERMINAL_NAME) {
                $table->cell($x, 0, $token);
                $offsetsX[$token] = $x;
                ++ $x;
            }
        }

        // left header
        $y = 1;
        /** @var State $state */
        foreach ($this->canonicalSituationSetFamily as $state) {
            $table->cell(0, $y, $state->getId());
            $offsetsY[$state->getId()] = $y;
            ++ $y;
        }

        // data
        foreach ($this->slrTable as $state => $row) {
            foreach ($row as $token => $action) {
                if ($this->showConflicts
                    && isset($this->conflicts[$state][$token])
                ) {
                    $text = implode('/', $this->conflicts[$state][$token]);
                } else {
                    $text = $action;
                }
                $table->cell($offsetsX[$token], $offsetsY[$state], $text);
            }
        }

        return (string) $table;
    }
}