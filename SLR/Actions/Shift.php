<?php
class SLR_Actions_Shift extends SLR_Actions_AbsAction
{
    public function getType()
    {
        return 'shift';
    }

    protected function prefix()
    {
        return 's';
    }

    public function execute(&$stack, &$input)
    {
        $stack[] = array_shift($input);
        $stack[] = $this->param;

        return $this->param;
    }
}
