<?php
class SLR_Actions_Transition extends SLR_Actions_AbsAction
{
    public function getType()
    {
        return 'transition';
    }

    protected function prefix()
    {
        return '';
    }

    public function execute(&$stack, &$input)
    {
        $stack[] = array_shift($input);
        $stack[] = $this->param;
        return $this->param;
    }
}