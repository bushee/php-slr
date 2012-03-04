<?php
class SLR_Actions_Accept extends SLR_Actions_AbsAction
{
    public function __construct()
    {
    }

    public function getType()
    {
        return 'accept';
    }

    protected function prefix()
    {
        return '';
    }

    public function execute(&$stack, &$input)
    {
        return true;
    }

    public function __toString()
    {
        return 'ACC';
    }
}