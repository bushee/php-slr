<?php
abstract class SLR_Actions_AbsAction
{
    protected $slr;
    protected $param;

    public function __construct(&$slr, $param)
    {
        $this->slr = $slr;
        $this->param = $param;
    }

    public function getParam()
    {
        return $this->param;
    }

    abstract public function getType();
    abstract protected function prefix();
    abstract public function execute(&$stack, &$input);

    public function __toString()
    {
        return $this->prefix() . $this->param;
    }
}