<?php
class SLR
{
	const START_TOKEN = '<start>';
	const END_TOKEN = '$';

	protected $startToken;
	protected $rulesByLefts;
	protected $rulesOrdered;
	protected $canonicalSituationSetFamily;
	protected $terminalTokens;
	protected $nonterminalTokens;

	public function __construct($config)
	{
		$this->startToken = $config['start'];
		$this->rulesByLefts = array();
		$this->rulesOrdered = array();

		$this->addStartRule();
		$tokens = $this->addRules($config['rules']);

		$this->divideTokens($tokens);

		$this->canonicalSituationSetFamily = new TransitionSet($this);
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
		$this->rulesOrdered[0] = $startRule;
	}

	protected function addRules($rules)
	{
		$tokens = array();

		$id = 1;
		foreach ($rules as $left => $rules)
		{
			foreach ($rules as $rule)
			{
				$rule = array(
					'id' => $id,
					'left' => $left,
					'right' => $rule[0],
					'callback' => $rule[1]
				);

				$this->rulesByLefts[$left][$id] = $rule;
				$this->rulesOrdered[$id] = $rule;

				foreach ($rule['right'] as $token)
				{
					$tokens[$token] = $token;
				}

				++ $id;
			}
		}

		return $tokens;
	}

	protected function divideTokens($tokens)
	{
		$this->terminalTokens = array();
		$this->nonterminalTokens = array();

		foreach ($tokens as $token)
		{
			if (isset($this->rulesByLefts[$token]))
			{
				$this->nonterminalTokens[] = $token;
			}
			else
			{
				$this->terminalTokens[] = $token;
			}
		}

		$this->terminalTokens[] = self::END_TOKEN;
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
		if (isset($token) && isset($this->rulesByLefts[$token]))
		{
			$ret = array_keys($this->rulesByLefts[$token]);
		}
		return $ret;
	}

	public function __toString()
	{
		$table = new TablePrinter();

		// top header
		$x = 1;
		$table->addBorder(1, TablePrinter::BORDER_HORIZONTAL);
		$table->addBorder($x);
		foreach ($this->terminalTokens as $token)
		{
			$table->cell($x, 0, $token);
			++ $x;
		}
		$table->addBorder($x);
		foreach ($this->nonterminalTokens as $token)
		{
			$table->cell($x, 0, $token);
			++ $x;
		}

		// left header
		$y = 1;
		foreach ($this->canonicalSituationSetFamily as $state)
		{
			$table->cell(0, $y, $state->getId());
			++ $y;
		}

		return $table->__toString();
	}
}

class TablePrinter
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
		if (!isset($this->data[$x]))
		{
			$this->data[$x] = array();
		}
		$this->data[$x][$y] = $value;

		$width = strlen($value);
		if (!isset($this->colWidths[$x]) || $this->colWidths[$x] < $width)
		{
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

		for ($y = 0; $y < $this->height; ++ $y)
		{
			if (isset($this->borders["h$y"]))
			{
				for ($x = 0; $x < $this->width; ++ $x)
				{
					if (isset($this->borders["v$x"]))
					{
						$s .= '|';
					}
					$padding = $this->colWidths[$x] + $this->padding;
					$s .= '|' . str_pad('', $padding, '-');
				}
				$s .= "|\n";
			}
			for ($x = 0; $x < $this->width; ++ $x)
			{
				if (isset($this->borders["v$x"]))
				{
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

class Situation
{
	public $slr;
	protected $rule;
	protected $dot;

	public function __construct(&$slr, $rule, $dot = 0)
	{
		$count = $slr->ruleLength($rule);
		if ($dot > $count)
		{
			throw new Exception("Dot may be on positions 0-$count in $count-token rule; $dot given.");
		}

		$this->slr = $slr;
		$this->rule = $rule;
		$this->dot = $dot;
	}

	public function getKey()
	{
		return $this->rule . '.' . $this->dot;
	}

	public function next()
	{
		if ($this->dot == $this->slr->ruleLength($this->rule))
		{
			return null;
		}
		else
		{
			$rule = $this->slr->rule($this->rule);
			return $rule['right'][$this->dot];
		}
	}

	public function step()
	{
		if ($this->dot == $this->slr->ruleLength($this->rule))
		{
			return false;
		}
		else
		{
			return new self($this->slr, $this->rule, $this->dot + 1);
		}
	}

	public function __toString()
	{
		$rule = $this->slr->rule($this->rule);
		$count = count($rule['right']);

		$ret = array($rule['left'], '->');
		for ($i = 0; $i < $count; ++ $i)
		{
			if ($this->dot == $i)
			{
				$ret[] = '*';
			}
			$ret[] = $rule['right'][$i];
		}
		if ($this->dot == $count)
		{
			$ret[] = '*';
		}

		return implode(' ', $ret);
	}

	public function closure()
	{
		$set = new SituationSet();
		$set->add($this);
		return $set->closure();
	}

	public function transition($token)
	{
		$set = new SituationSet();
		$set->add($this);
		return $set->transition($token);
	}
}

class SituationSet implements ArrayAccess, Iterator
{
	protected $set;
	protected $key;
	protected $current;

	public function __construct()
	{
		$this->set = array();
		$this->invalidateKey();
	}

	public function offsetExists($offset)
	{
		return isset($this->set[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->set[$offset];
	}

	public function offsetSet($offset, $value)
	{
		$this->invalidateKey();
		return $this->set[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->set[$offset]);
	}

	public function add($situation)
	{
		$key = $situation->getKey();
		if (isset($this->set[$key]))
		{
			return false;
		}
		else
		{
			$this->invalidateKey();
			$this->set[$key] = $situation;
			return true;
		}
	}

	public function current()
	{
		return current($this->set);
	}

	public function key()
	{
		return key($this->set);
	}

	public function next()
	{
		next($this->set);
	}

	public function rewind()
	{
		reset($this->set);
	}

	public function valid()
	{
		return key($this->set) !== null;
	}

	public function closure()
	{
		$situations = $this->set;
		$set = new self();

		while (!empty($situations))
		{
			$situation = array_shift($situations);
			if ($set->add($situation))
			{
				$slr = &$situation->slr;
				foreach ($slr->rulesOf($situation->next()) as $id)
				{
					$situations[] = new Situation($slr, $id, 0);
				}
			}
		}

		return $set;
	}

	public function transition($token)
	{
		$set = new self();

		foreach ($this->set as $situation)
		{
			if ($situation->next() == $token)
			{
				$next = $situation->step();
				if ($next)
				{
					$set->add($next);
				}
			}
		}

		return $set->closure();
	}

	public function nextTokens()
	{
		$next = array();

		foreach ($this->set as $situation)
		{
			$token = $situation->next();
			if (isset($token))
			{
				$next[$token] = $token;
			}
		}

		return $next;
	}

	public function equals($set)
	{
		return $this->getKey() == $set->getKey();
	}

	public function getKey()
	{
		if (!isset($this->key))
		{
			$keys = array();
			foreach ($this->set as $situation)
			{
				$key = $situation->getKey();
				$keys[$key] = $key;
			}
			sort($keys);
			$this->key = implode('|', $keys);
		}

		return $this->key;
	}

	public function invalidateKey()
	{
		$this->key = null;
	}

	public function __toString()
	{
		$ret = '';

		foreach ($this->set as $situation)
		{
			$ret .= "$situation\n";
		}

		return $ret;
	}
}

class TransitionSet implements Iterator
{
	protected $states;
	protected $stateIds;

	public function __construct(&$slr)
	{
		$this->states = array();
		$this->stateIds = array();

		$situation = new Situation($slr, 0, 0);
		$closure = $situation->closure();

		$this->addState($closure);

		foreach ($this as $state)
		{
			$set = $state->getSet();
			foreach ($set->nextTokens() as $next)
			{
				$state->addTransition($next, $this->addState($set->transition($next)));
			}
		}
	}

	protected function addState($situationSet, $addState = true)
	{
		$id = false;

		if (isset($this->stateIds[$situationSet->getKey()]))
		{
			$id = $this->stateIds[$situationSet->getKey()];
		}
		elseif ($addState)
		{
			$state = new State($situationSet);
			$id = $state->getId();

			$this->states[$id] = $state;
			$this->stateIds[$situationSet->getKey()] = $id;
		}

		return $id;
	}

	public function current()
	{
		return current($this->states);
	}

	public function key()
	{
		return key($this->states);
	}

	public function next()
	{
		next($this->states);
	}

	public function rewind()
	{
		reset($this->states);
	}

	public function valid()
	{
		return key($this->states) !== null;
	}

	public function __toString()
	{
		$ret = '';

		foreach ($this->states as $state)
		{
			$ret .= "$state\n";
		}

		return $ret;
	}
}

class State
{
	protected $id;
	protected $set;
	protected $transitions;

	protected static $count = 0;

	public function __construct($set)
	{
		$this->id = self::$count ++;
		$this->set = $set;
		$this->transitions = array();
	}

	public function getId()
	{
		return $this->id;
	}

	public function getSet()
	{
		return $this->set;
	}

	public function addTransition($token, $state)
	{
		if (isset($this->transitions[$token]))
		{
			throw new Exception("This state already has a transition for token '$token'.");
		}

		$this->transitions[$token] = $state;
	}

	public function __toString()
	{
		$ret = 'state ' . $this->id . ":\n";
		$ret .= $this->set;
		$ret .= "transitions:\n";
		foreach ($this->transitions as $token => $next)
		{
			$ret .= "$token -> $next\n";
		}
		$ret .= "\n";
		return $ret;
	}
}