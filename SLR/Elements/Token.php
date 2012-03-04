<?php
class SLR_Elements_Token
{
    const UNRECOGNIZED_TOKEN = 'T_UNRECOGNIZED_TOKEN';

    protected $type;
    protected $value;
    protected $state;

    public function __construct($type, $value = null, $state = null)
    {
        $this->type = $type;
        $this->value = $value;
        $this->state = $state;
    }

    public static function getUnrecognizedToken($value = null, $state = null)
    {
        return new self(self::UNRECOGNIZED_TOKEN, $value, $state);
    }

    public function type()
    {
        return $this->type;
    }

    public function value()
    {
        return $this->value;
    }

    public function state()
    {
        return $this->state;
    }

    public function __toString()
    {
        $s = $this->type;
        $additional = array();
        if (isset($this->value)) {
            $additional[] = '"' . $this->value . '"';
        }
        if (isset($this->state)) {
            $additional[] = '@' . $this->state;
        }
        if (!empty($additional)) {
            $s .= ' (' . implode(' ', $additional) . ')';
        }
        return $s;
    }
}