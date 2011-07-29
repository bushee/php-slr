<?php
class Parser
{
	protected $slr;

	public function __construct($slr)
	{
		$this->slr = $slr;
	}

	public function parse($tokens)
	{
		$endToken = $this->slr->getEndToken();
		$tokens[] = $endToken;

		$state = $this->slr->getStartState();
		$stack = array(
			$state
		);

		while(true)
		{
			$next = $tokens[0];
			$action = $this->slr->actionFor($state, $next->type());

			if ($action)
			{
				$result = $action->execute($stack, $tokens);

				if ($result === true)
				{
					return $this->slr->success($stack[1]->value());
				}
				else
				{
					$state = $result;
				}
			}
			else
			{
				if ($next == $endToken)
				{
					// TODO this exception should carry list of expected tokens
					$exception = new Exception('unfinished');
				}
				else
				{
					// TODO this exception should carry list of expected tokens
					$exception = new Exception("Can't consume $next");
				}
				return $this->slr->failure($exception);
			}
		}
	}
}

class Token
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
		if (isset($this->value))
		{
			$additional[] = '"' . $this->value . '"';
		}
		if (isset($this->state))
		{
			$additional[] = '@' . $this->state;
		}
		if (!empty($additional))
		{
			$s .= ' (' . implode(' ', $additional) . ')';
		}
		return $s;
	}
}

class SLR
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

		$this->canonicalSituationSetFamily = new TransitionSet($this);

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
		foreach ($rules as $left => $rules)
		{
			foreach ($rules as $rule)
			{
				$right = $rule[0];
				if (empty($right))
				{
					$right = array(self::EPSILON_TOKEN);
				}

				if (is_callable($rule[1]))
				{
					$callback = $rule[1];
				}
				else
				{
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

				foreach ($right as $token)
				{
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

		foreach ($tokens as $token)
		{
			if (isset($this->rulesByLefts[$token]))
			{
				$this->nonterminalTokens[$token] = $token;
			}
			else
			{
				$this->terminalTokens[$token] = $token;
			}
		}

		$this->terminalTokens[self::END_TOKEN] = self::END_TOKEN;
		$this->terminalTokens[self::EPSILON_TOKEN] = self::EPSILON_TOKEN;
	}

	protected function first($token, $visited = array())
	{
		$visited[$token] = $token;

		if (!isset($this->first[$token]))
		{
			if ($this->isTerminal($token))
			{
				$this->first[$token] = array(
					$token => $token
				);
			}
			else
			{
				$first = array();

				foreach ($this->rulesByLefts[$token] as $rule)
				{
					if (count($rule['right']) == 1 && $rule['right'][0] == self::EPSILON_TOKEN)
					{
						// if X -> epsilon, then epsilon is in first(X)
						$first[self::EPSILON_TOKEN] = self::EPSILON_TOKEN;
					}
					else
					{
						$epsilonCounter = 0;
						foreach ($rule['right'] as $right)
						{
							// avoid cycles
							if (isset($visited[$right]))
							{
								continue;
							}

							$rightFirst = $this->first($right);
							$epsilon = isset($rightFirst[self::EPSILON_TOKEN]);
							unset($rightFirst[self::EPSILON_TOKEN]);

							// first(Yi)\{epsilon} is in first(X)
							$first = array_merge($first, $rightFirst);
							if ($epsilon)
							{
								++ $epsilonCounter;
							}
							else
							{
								break;
							}
						}

						// if epsilon is in first(Yi) for all i, epsilon is in first(X)
						if ($epsilonCounter == count($rule['right']))
						{
							$first[self::EPSILON_TOKEN] = self::EPSILON_TOKEN;
						}
					}
				}

				$this->first[$token] = $first;
			}
		}

		return $this->first[$token];
	}

	protected function follow($token, $visited = array())
	{
		$visited[$token] = $token;

		if (!isset($this->follow[$token]))
		{
			$follow = array();

			if ($token == self::START_TOKEN)
			{
				$follow[self::END_TOKEN] = self::END_TOKEN;
			}
			else
			{
				foreach ($this->rulesByRights[$token] as $rule)
				{
					$length = count($rule['right']);
					foreach ($rule['right'] as $key => $rightToken)
					{
						if ($rightToken == $token)
						{
							$epsilon = false;
							if ($key + 1 < $length)
							{
								$first = $this->first($rule['right'][$key + 1]);
								$epsilon = isset($first[self::EPSILON_TOKEN]);
								unset($first[self::EPSILON_TOKEN]);
								$follow = array_merge($follow, $first);
							}
							if ($epsilon || !($key + 1 < $length))
							{
								if (!isset($visited[$rule['left']]))
								{
									$follow = array_merge($follow, $this->follow($rule['left']));
								}
							}
						}
					}
				}
			}

			$this->follow[$token] = $follow;
		}

		return $this->follow[$token];
	}

	protected function calculateSlrTable()
	{
		$table = array();

		foreach ($this->canonicalSituationSetFamily as $state)
		{
			$row = array();

			foreach ($state->getTransitions() as $token => $nextState)
			{
				if ($this->isTerminal($token))
				{
					$row[$token] = new ShiftAction($this, $nextState);
				}
				else
				{
					$row[$token] = new TransitionAction($this, $nextState);
				}
			}
			foreach ($state->getSet() as $situation)
			{
				if (!$situation->hasNext())
				{
					$rule = $situation->getRule();
					if ($rule['left'] == self::START_TOKEN)
					{
						$action = new AcceptAction();
					}
					else
					{
						$action = new ReduceAction($this, $rule['id']);
					}

					foreach ($this->follow($rule['left']) as $token)
					{
						if (isset($row[$token]))
						{
							// conflict:
							// 1. store info about it
							if (isset($this->conflicts[$state->getId()][$token]))
							{
								$this->conflicts[$state->getId()][$token][] = $action;
							}
							else
							{
								$this->conflicts[$state->getId()][$token] = array(
									$row[$token], $action
								);
							}
							// 2. resolve it by default rule
							switch ($row[$token]->getType())
							{
								case 'shift':
									// do nothing - shift remains
									break;
								case 'reduce':
									// select rule with lesser id
									if ($action->getParam() < $row[$token]->getParam())
									{
										$row[$token] = $action;
									}
									break;
							}
						}
						else
						{
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
		if (isset($config['success']) && is_callable($config['success']))
		{
			$this->success = $config['success'];
		}
		else
		{
			$this->success = array(self, 'defaultSuccess');
		}

		if (isset($config['failure']) && is_callable($config['failure']))
		{
			$this->failure = $config['failure'];
		}
		else
		{
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
		if (isset($token) && isset($this->rulesByLefts[$token]))
		{
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
		return new Token(self::END_TOKEN);
	}

	public function setShowConflicts($value)
	{
		$this->showConflicts = $value;
	}

	public function __toString()
	{
		$table = new TablePrinter();

		$offsetsX = array();
		$offsetsY = array();

		// top header
		$x = 1;
		$table->addBorder(1, TablePrinter::BORDER_HORIZONTAL);
		$table->addBorder($x);
		foreach ($this->terminalTokens as $token)
		{
			if ($token != self::EPSILON_TOKEN)
			{
				$table->cell($x, 0, $token);
				$offsetsX[$token] = $x;
				++ $x;
			}
		}
		$table->addBorder($x);
		foreach ($this->nonterminalTokens as $token)
		{
			if ($token != self::START_TOKEN)
			{
				$table->cell($x, 0, $token);
				$offsetsX[$token] = $x;
				++ $x;
			}
		}

		// left header
		$y = 1;
		foreach ($this->canonicalSituationSetFamily as $state)
		{
			$table->cell(0, $y, $state->getId());
			$offsetsY[$state->getId()] = $y;
			++ $y;
		}

		// data
		foreach ($this->slrTable as $state => $row)
		{
			foreach ($row as $token => $action)
			{
				if ($this->showConflicts && isset($this->conflicts[$state][$token]))
				{
					$text = implode('/', $this->conflicts[$state][$token]);
				}
				else
				{
					$text = $action;
				}
				$table->cell($offsetsX[$token], $offsetsY[$state], $text);
			}
		}

		return (string) $table;
	}
}

abstract class Action
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

class ShiftAction extends Action
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

class ReduceAction extends Action
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

		for ($i = count($rule['right']) - 1; $i >= 0; -- $i)
		{
			while (!empty($stack))
			{
				$element = array_pop($stack);
				if (is_a($element, 'Token'))
				{
					if ($element->type() == $rule['right'][$i])
					{
						array_unshift($right, $element->value());
						break;
					}
					else
					{
						throw new Exception('Parser was compiled with errors...');
					}
				}
			}
		}
		if (empty($stack))
		{
			throw new Exception('Parser was compiled with errors...');
		}
		else
		{
			$value = call_user_func($rule['callback'], $right);
			array_unshift($input, new Token($rule['left'], $value));
			return $stack[count($stack) - 1];
		}
	}
}

class TransitionAction extends Action
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

class AcceptAction extends Action
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
		// just to make sure
		$value = (string) $value;

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

	public function getRuleId()
	{
		return $this->rule;
	}

	public function getRule()
	{
		return $this->slr->rule($this->rule);
	}

	public function hasNext()
	{
		return $this->dot < $this->slr->ruleLength($this->rule);
	}

	public function next()
	{
		if (!$this->hasNext())
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
		if (!$this->hasNext())
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
	protected $startState;

	public function __construct(&$slr)
	{
		$this->states = array();
		$this->stateIds = array();

		$situation = new Situation($slr, 0, 0);
		$closure = $situation->closure();

		$this->startState = $this->addState($closure);

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

	public function getStartState()
	{
		return $this->startState;
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

	public function getTransitions()
	{
		return $this->transitions;
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

class Lexer
{
	protected $rules;
	protected $useUnrecognizedToken;

	public function __construct($config, $useUnrecognizedToken = true)
	{
		$this->useUnrecognizedToken = $useUnrecognizedToken;

		$this->rules = array('all' => array());
		foreach ($config as $state => $rules)
		{
			foreach ($rules as $rule)
			{
				if (is_callable($rule[2]))
				{
					$callback = $rule[2];
				}
				else
				{
					$callback = array(self, 'defaultCallback');
				}

				$this->rules[$state][] = array(
					'matcher' => Matcher::getMatcher($rule[0], $rule[1]),
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

		while ($offset < $length)
		{
			$rules = array_merge($this->rules[$currentState], $this->rules['all']);
			$matched = false;

			foreach ($rules as $rule)
			{
				$matched = $rule['matcher']->match($string, $offset);
				if ($matched !== false)
				{
					$this->checkUnrecognized(&$unrecognized, &$tokens);

					$offset += strlen($matched);
					$type = call_user_func($rule['callback'], &$matched);
					$tokens[] = new Token($type, $matched, $currentState);

					if ($rule['stateSwitch'])
					{
						if ($rule['stateSwitch'] == 'previous')
						{
							if (count($stateStack) == 1)
							{
								throw new Exception('Can\'t go to previous state anymore - state stack is empty.');
							}
							array_pop($stateStack);
							$currentState = $stateStack[count($stateStack) - 1];
						}
						else
						{
							$currentState = $rule['stateSwitch'];
							$stateStack[] = $currentState;
						}
					}

					$matched = true;

					break;
				}
			}

			if (!$matched)
			{
				$unrecognized .= $string[$offset];
				++ $offset;
			}
		}
		$this->checkUnrecognized(&$unrecognized, &$tokens);

		return $tokens;
	}

	protected function checkUnrecognized(&$unrecognized, &$tokens)
	{
		if (isset($unrecognized))
		{
			if ($this->useUnrecognizedToken)
			{
				$tokens[] = Token::getUnrecognizedToken($unrecognized, $currentState);
				$unrecognized = null;
			}
			else
			{
				throw new Exception("Unrecognized token: \"$unrecognized\"");
			}
		}
	}
}

abstract class Matcher
{
	protected $pattern;

	public function __construct($pattern)
	{
		$this->pattern = $pattern;
	}

	public static function getMatcher($type, $pattern)
	{
		$className = ucfirst($type) . 'Matcher';
		if (class_exists($className))
		{
			return new $className($pattern);
		}
		else
		{
			throw new Exception("Matcher \"$type\" doesn't exist.");
		}
	}

	public abstract function match(&$string, $offset);
}

class StringMatcher extends Matcher
{
	public function match(&$string, $offset)
	{
		if (substr($string, $offset, strlen($this->pattern)) == $this->pattern)
		{
			return $this->pattern;
		}
		else
		{
			return false;
		}
	}
}

class RegexMatcher extends Matcher
{
	public function match(&$string, $offset)
	{
		preg_match($this->pattern, $string, $matches, PREG_OFFSET_CAPTURE, $offset);
		if (count($matches) > 0 && $matches[0][1] == $offset)
		{
			return $matches[0][0];
		}
		else
		{
			return false;
		}
	}
}