<?php
class SLR_Actions_Reduce extends SLR_Actions_AbsAction
{
    public function getType()
    {
        return 'reduce';
    }

    protected function prefix()
    {
        return 'r';
    }

    public function execute(&$stack, &$input)
    {
        $rule = $this->slr->rule($this->param);
        $right = array();

        for ($i = count($rule['right']) - 1; $i >= 0; -- $i) {
            while (!empty($stack)) {
                $element = array_pop($stack);
                if (is_a($element, 'SLR_Elements_Token')) {
                    if ($element->type() == $rule['right'][$i]) {
                        array_unshift($right, $element->value());
                        break;
                    } else {
                        throw new Exception('Parser was compiled with errors...');
                    }
                }
            }
        }
        if (empty($stack)) {
            throw new Exception('Parser was compiled with errors...');
        } else {
            $value = call_user_func($rule['callback'], $right);
            array_unshift($input, new SLR_Elements_Token($rule['left'], $value));
            return $stack[count($stack) - 1];
        }
    }
}