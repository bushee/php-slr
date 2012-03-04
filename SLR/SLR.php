<?php
class SLR_SLR
{
    const START_TOKEN = '<start>';
    const EPSILON_TOKEN = '<epsilon>';
    const END_TOKEN = '$';

    protected $startToken;
    protected $rulesByLefts;
    protected $rulesByRights;
    protected $rulesOrdered;
    protected $canonicalSituationSetFamily;
    protected $terminalTokens;
    protected $nonterminalTokens;

    protected $first;
    protected $follow;
    protected $slrTable;

    protected $showConflicts;
    protected $conflicts;

    protected $success;
    protected $failure;

    public function __construct($config)
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

        $this->canonicalSituationSetFamily = new SLR_Elements_TransitionSet($this);

        $this->slrTable = $this->calculateSlrTable();

        $this->addEndCallbacks($config);
    }

    protected function addStartRule()
    {
        $startRule = array(
            'id' => 0,
            'left' => self::START_TOKEN,
            'right' => array($this->startToken),
            'callback' => ''
        );
        $this->rulesByLefts[self::START_TOKEN][0] = $startRule;
        $this->rulesByRights[$this->startToken][0] = $startRule;
        $this->rulesOrdered[0] = $startRule;
    }

    protected function addRules($rules)
    {
        $tokens = array();

        $id = 1;
        foreach ($rules as $left => $rules) {
            foreach ($rules as $rule) {
                $right = $rule[0];
                if (empty($right)) {
                    $right = array(self::EPSILON_TOKEN);
                }

                if (is_callable($rule[1])) {
                    $callback = $rule[1];
                } else {
                    $callback = array(self, 'defaultCallback');
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

    public static function defaultCallback($tokens)
    {
        return $tokens[0];
    }

    protected function divideTokens($tokens)
    {
        $this->terminalTokens = array(
        );
        $this->nonterminalTokens = array(
            self::START_TOKEN => self::START_TOKEN
        );

        foreach ($tokens as $token) {
            if (isset($this->rulesByLefts[$token])) {
                $this->nonterminalTokens[$token] = $token;
            } else {
                $this->terminalTokens[$token] = $token;
            }
        }

        $this->terminalTokens[self::END_TOKEN] = self::END_TOKEN;
        $this->terminalTokens[self::EPSILON_TOKEN] = self::EPSILON_TOKEN;
    }

    protected function first($token, $visited = array())
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
                if (count($rule['right']) == 1 && $rule['right'][0] == self::EPSILON_TOKEN) {
                    // if X -> epsilon, then epsilon is in first(X)
                    $first[self::EPSILON_TOKEN] = self::EPSILON_TOKEN;
                } else {
                    $epsilonCounter = 0;
                    foreach ($rule['right'] as $right) {
                        // avoid cycles
                        if (isset($visited[$right])) {
                            if ($right != $token) {
                                // if first(X) for current X depends on any other tokens, don't save it - it may be incomplete
                                $save = false;
                            }

                            continue;
                        }

                        $rightFirst = $this->first($right, $visited);
                        $epsilon = isset($rightFirst[self::EPSILON_TOKEN]);
                        unset($rightFirst[self::EPSILON_TOKEN]);

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
                        $first[self::EPSILON_TOKEN] = self::EPSILON_TOKEN;
                    }
                }

                if ($save) {
                    $this->first[$token] = $first;
                }
            }
        }

        return $first;
    }

    protected function follow($token, $visited = array())
    {
        $visited[$token] = $token;

        if (isset($this->follow[$token])) {
            $follow = $this->follow[$token];
        } elseif ($token == self::START_TOKEN) {
            $follow = array(self::END_TOKEN);
            $this->follow[self::START_TOKEN] = $follow;
        } else {
            $follow = array();
            $save = true;

            foreach ($this->rulesByRights[$token] as $rule) {
                $length = count($rule['right']);
                foreach ($rule['right'] as $key => $rightToken) {
                    if ($rightToken == $token) {
                        $epsilon = false;
                        if ($key + 1 < $length) {
                            $first = $this->first($rule['right'][$key + 1]);
                            $epsilon = isset($first[self::EPSILON_TOKEN]);
                            unset($first[self::EPSILON_TOKEN]);
                            $follow = array_merge($follow, $first);
                        }
                        if ($epsilon || !($key + 1 < $length)) {
                            // avoid cycles
                            if (!isset($visited[$rule['left']])) {
                                $follow = array_merge($follow, $this->follow($rule['left'], $visited));
                            } elseif ($token != $rule['left']) {
                                // if follow(X) for current X depends on any other tokens, don't save it - it may be incomplete
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

    protected function calculateSlrTable()
    {
        $table = array();

        foreach ($this->canonicalSituationSetFamily as $state) {
            $row = array();

            foreach ($state->getTransitions() as $token => $nextState) {
                if ($this->isTerminal($token)) {
                    $row[$token] = new SLR_Actions_Shift($this, $nextState);
                } else {
                    $row[$token] = new SLR_Actions_Transition($this, $nextState);
                }
            }
            foreach ($state->getSet() as $situation) {
                if (!$situation->hasNext()) {
                    $rule = $situation->getRule();
                    if ($rule['left'] == self::START_TOKEN) {
                        $action = new SLR_Actions_Accept();
                    } else {
                        $action = new SLR_Actions_Reduce($this, $rule['id']);
                    }

                    foreach ($this->follow($rule['left']) as $token) {
                        if (isset($row[$token])) {
                            // conflict:
                            // 1. store info about it
                            if (isset($this->conflicts[$state->getId()][$token])) {
                                $this->conflicts[$state->getId()][$token][] = $action;
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

    protected function addEndCallbacks($config)
    {
        if (isset($config['success']) && is_callable($config['success'])) {
            $this->success = $config['success'];
        } else {
            $this->success = array(self, 'defaultSuccess');
        }

        if (isset($config['failure']) && is_callable($config['failure'])) {
            $this->failure = $config['failure'];
        } else {
            $this->failure = array(self, 'defaultFailure');
        }
    }

    public function defaultSuccess($value)
    {
        return $value;
    }

    public function defaultFailure($exception)
    {
        throw $exception;
    }

    public function success($value)
    {
        return call_user_func($this->success, $value);
    }

    public function failure($exception)
    {
        return call_user_func($this->failure, $exception);
    }

    public function actionFor($state, $token)
    {
        return $this->slrTable[$state][$token];
    }

    public function rule($id)
    {
        return $this->rulesOrdered[$id];
    }

    public function ruleLength($id)
    {
        return count($this->rulesOrdered[$id]['right']);
    }

    public function rulesOf($token)
    {
        $ret = array();
        if (isset($token) && isset($this->rulesByLefts[$token])) {
            $ret = array_keys($this->rulesByLefts[$token]);
        }
        return $ret;
    }

    public function isTerminal($token)
    {
        return isset($this->terminalTokens[$token]);
    }

    public function isNonterminal($token)
    {
        return isset($this->nonterminalTokens[$token]);
    }

    public function getStartState()
    {
        return $this->canonicalSituationSetFamily->getStartState();
    }

    public function getEndToken()
    {
        return new SLR_Elements_Token(self::END_TOKEN);
    }

    public function setShowConflicts($value)
    {
        $this->showConflicts = $value;
    }

    public function __toString()
    {
        $table = new SLR_Utils_TablePrinter();

        $offsetsX = array();
        $offsetsY = array();

        // top header
        $x = 1;
        $table->addBorder(1, SLR_Utils_TablePrinter::BORDER_HORIZONTAL);
        $table->addBorder($x);
        foreach ($this->terminalTokens as $token) {
            if ($token != self::EPSILON_TOKEN) {
                $table->cell($x, 0, $token);
                $offsetsX[$token] = $x;
                ++ $x;
            }
        }
        $table->addBorder($x);
        foreach ($this->nonterminalTokens as $token) {
            if ($token != self::START_TOKEN) {
                $table->cell($x, 0, $token);
                $offsetsX[$token] = $x;
                ++ $x;
            }
        }

        // left header
        $y = 1;
        foreach ($this->canonicalSituationSetFamily as $state) {
            $table->cell(0, $y, $state->getId());
            $offsetsY[$state->getId()] = $y;
            ++ $y;
        }

        // data
        foreach ($this->slrTable as $state => $row) {
            foreach ($row as $token => $action) {
                if ($this->showConflicts && isset($this->conflicts[$state][$token])) {
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