<?php
class SLR_Lexer
{
    protected $rules;
    protected $useUnrecognizedToken;

    public function __construct($config, $useUnrecognizedToken = true)
    {
        $this->useUnrecognizedToken = $useUnrecognizedToken;

        $this->rules = array('all' => array());
        foreach ($config as $state => $rules) {
            foreach ($rules as $rule) {
                if (is_callable($rule[2])) {
                    $callback = $rule[2];
                } else {
                    $callback = array(self, 'defaultCallback');
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

    public static function defaultCallback()
    {
        return null;
    }

    public function lex($string)
    {
        $offset = 0;
        $length = strlen($string);
        $currentState = 'initial';
        $stateStack = array($currentState);
        $tokens = array();

        $unrecognized = null;

        while ($offset < $length) {
            $rules = array_merge($this->rules[$currentState], $this->rules['all']);
            $matched = false;

            foreach ($rules as $rule) {
                $matched = $rule['matcher']->match($string, $offset);
                if ($matched !== false) {
                    $this->checkUnrecognized(&$unrecognized, &$tokens);

                    $offset += strlen($matched);
                    $type = call_user_func($rule['callback'], &$matched);
                    $tokens[] = new SLR_Elements_Tokens_Token($type, $matched, $currentState);

                    if ($rule['stateSwitch']) {
                        if ($rule['stateSwitch'] == 'previous') {
                            if (count($stateStack) == 1) {
                                throw new Exception('Can\'t go to previous state anymore - state stack is empty.');
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
        $this->checkUnrecognized(&$unrecognized, &$tokens);

        return $tokens;
    }

    protected function checkUnrecognized(&$unrecognized, &$tokens)
    {
        if (isset($unrecognized)) {
            if ($this->useUnrecognizedToken) {
                $tokens[] = new SLR_Elements_Tokens_Unrecognized(
                    $unrecognized, $currentState
                );
                $unrecognized = null;
            } else {
                throw new Exception("Unrecognized token: \"$unrecognized\"");
            }
        }
    }
}
