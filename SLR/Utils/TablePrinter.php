<?php
class SLR_Utils_TablePrinter
{
    const BORDER_VERTICAL = 0;
    const BORDER_HORIZONTAL = 1;

    protected $data;
    protected $colWidths;
    protected $borders;
    protected $padding;
    protected $width;
    protected $height;

    public function __construct($padding = 2, $width = 0, $height = 0)
    {
        $this->data = array();
        $this->colWidths = array();
        $this->borders = array();
        $this->padding = $padding;
        $this->width = $width;
        $this->height = $height;
    }

    public function cell($x, $y, $value)
    {
        // just to make sure
        $value = (string) $value;

        if (!isset($this->data[$x])) {
            $this->data[$x] = array();
        }
        $this->data[$x][$y] = $value;

        $width = strlen($value);
        if (!isset($this->colWidths[$x]) || $this->colWidths[$x] < $width) {
            $this->colWidths[$x] = $width;
        }

        $this->width = max($x + 1, $this->width);
        $this->height = max($y + 1, $this->height);
    }

    public function addBorder($x, $type = self::BORDER_VERTICAL)
    {
        $t = $this->getBorderType($type);
        $this->borders["$t$x"] = true;
    }

    public function removeBorder($x, $type = self::BORDER_VERTICAL)
    {
        $t = $this->getBorderType($type);
        unset($this->borders["$t$x"]);
    }

    private function getBorderType($type)
    {
        switch ($type)
        {
            case self::BORDER_HORIZONTAL:
                return 'h';
            case self::BORDER_VERTICAL:
                return 'v';
            default:
                throw new Exception("Unknown border type: $type");
        }
    }

    public function setPadding($padding)
    {
        $this->padding = $padding;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function __toString()
    {
        $s = '';

        for ($y = 0; $y < $this->height; ++ $y) {
            if (isset($this->borders["h$y"])) {
                for ($x = 0; $x < $this->width; ++ $x) {
                    if (isset($this->borders["v$x"])) {
                        $s .= '|';
                    }
                    $padding = $this->colWidths[$x] + $this->padding;
                    $s .= '|' . str_pad('', $padding, '-');
                }
                $s .= "|\n";
            }
            for ($x = 0; $x < $this->width; ++ $x) {
                if (isset($this->borders["v$x"])) {
                    $s .= '|';
                }
                $padding = $this->colWidths[$x] + $this->padding;
                $s .= '|' . str_pad($this->data[$x][$y], $padding, ' ', STR_PAD_BOTH);
            }
            $s .= "|\n";
        }

        return $s;
    }
}
