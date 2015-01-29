<?php

namespace EUREKA\G6KBundle\Entity;

class Token {

    const	T_UNDEFINED    		= 0,
			T_NUMBER      		= 1,  
			T_DATE        		= 2, 
			T_BOOLEAN        	= 3, 
			T_TEXT		       	= 4, 
			T_IDENT       		= 5,  
			T_FUNCTION    		= 6,  
			T_POPEN       		= 7,  
			T_PCLOSE      		= 8, 
			T_COMMA       		= 9, 
			T_NOOP	    		= 10, 
			T_PLUS        		= 11, 
			T_MINUS       		= 12, 
			T_TIMES      	 	= 13, 
			T_DIV         		= 14, 
			T_MOD         		= 15, 
			T_POW         		= 16, 
			T_UNARY_PLUS  		= 17, 
			T_UNARY_MINUS 		= 18, 
			T_NOT         		= 19, 
			T_FIELD       		= 20, 
			T_EQUAL				= 21,
			T_NOT_EQUAL			= 22,
			T_LESS_THAN			= 23,
			T_LESS_OR_EQUAL		= 24,
			T_GREATER_THAN		= 25,
			T_GREATER_OR_EQUAL	= 26,
			T_BITWISE_AND		= 27,
			T_BITWISE_OR		= 28,
			T_BITWISE_XOR		= 29,
			T_LOGICAL_AND		= 30,
			T_LOGICAL_OR		= 31,
			T_TERNARY			= 32,
			T_TERNARY_ELSE		= 33,
			T_DEGRE				= 34;

	const	A_NONE				= 0,
			A_LEFT				= 1,
			A_RIGHT				= 2;
	
    public $type, $value;

    public function __construct($type, $value) {
        $this->type  = $type;
        $this->value = $value;
    }
	
    public function isUnaryOperator(){
        switch ($this->type) {
            case self::T_NOT:
            case self::T_UNARY_PLUS:
            case self::T_UNARY_MINUS:
            case self::T_TERNARY_ELSE:
            case self::T_DEGRE:
                return true;
        }
		return false;
	}
	
    public function isBinaryOperator(){
        switch ($this->type) {
            case self::T_POW:
            case self::T_TIMES:
            case self::T_DIV:
            case self::T_MOD:
            case self::T_PLUS:
            case self::T_MINUS:
            case self::T_BITWISE_AND:
            case self::T_BITWISE_OR:
            case self::T_BITWISE_XOR:
            case self::T_LOGICAL_AND:
            case self::T_LOGICAL_OR:
               return true;
        }
		return false;
	}
	
    public function isTernaryOperator(){
        switch ($this->type) {
            case self::T_TERNARY:
               return true;
        }
		return false;
	}
	
    public function isOperator(){
		return $this->isUnaryOperator() 
			|| $this->isBinaryOperator() 
			|| $this->isTernaryOperator();
	}
	
    public function isComparator(){
        switch ($this->type) {
            case self::T_EQUAL:
            case self::T_NOT_EQUAL:
            case self::T_LESS_THAN:
            case self::T_LESS_OR_EQUAL:
            case self::T_GREATER_THAN:
            case self::T_GREATER_OR_EQUAL:
                return true;
        }
		return false;
	}
	
    public function isVariable(){
        switch ($this->type) {
            case self::T_IDENT:
            case self::T_FIELD:
            case self::T_UNDEFINED:
                return true;
        }
		return false;
	}
	
    public function isUndefined(){
		return $this->type == self::T_UNDEFINED;
	}
		
	public function isBeforeFunctionArgument(){
        switch ($this->type) {
	        case self::T_POPEN:
	        case self::T_COMMA:
	        case self::T_NOOP:
	            return true;
	    }
		return false;
	}
	
    public function precedence(){
        switch ($this->type) {
            case self::T_POPEN:
            case self::T_PCLOSE:
            case self::T_POW:
                return 1;
            case self::T_NOT:
            case self::T_UNARY_PLUS:
            case self::T_UNARY_MINUS:
            case self::T_DEGRE:
                return 2;
            case self::T_TIMES:
            case self::T_DIV:
            case self::T_MOD:
                return 3;
            case self::T_PLUS:
            case self::T_MINUS:
                return 4;
            case self::T_LESS_THAN:
            case self::T_LESS_OR_EQUAL:
            case self::T_GREATER_THAN:
            case self::T_GREATER_OR_EQUAL:
               return 6;
            case self::T_EQUAL:
            case self::T_NOT_EQUAL:
               return 7;
            case self::T_BITWISE_AND:
               return 8;
            case self::T_BITWISE_XOR:
               return 9;
            case self::T_BITWISE_OR:
               return 10;
            case self::T_LOGICAL_AND:
               return 11;
            case self::T_LOGICAL_OR:
               return 12;
            case self::T_TERNARY_ELSE:
               return 13;
            case self::T_TERNARY:
               return 14;
            case self::T_COMMA:
               return 15;
        }

        return 16;
    }
	
    public function associativity(){
        switch ($this->type) {
            case self::T_POW:
            case self::T_NOT:
            case self::T_UNARY_PLUS:
            case self::T_UNARY_MINUS:
                return self::A_RIGHT;
            case self::T_DEGRE:
            case self::T_TIMES:
            case self::T_DIV:
            case self::T_MOD:
            case self::T_PLUS:
            case self::T_MINUS:
            case self::T_LESS_THAN:
            case self::T_LESS_OR_EQUAL:
            case self::T_GREATER_THAN:
            case self::T_GREATER_OR_EQUAL:
            case self::T_EQUAL:
            case self::T_NOT_EQUAL:
            case self::T_BITWISE_AND:
            case self::T_BITWISE_XOR:
             case self::T_BITWISE_OR:
            case self::T_LOGICAL_AND:
            case self::T_LOGICAL_OR:
            case self::T_TERNARY:
                return self::A_LEFT;
            case self::T_TERNARY_ELSE:
                return self::A_RIGHT;
            case self::T_COMMA:
                return self::A_LEFT;
        }

        return self::A_NONE;
    }
	
	public function __toString() {
		switch ($this->type) {
			case self::T_DATE:
				return $this->value->format("d/m/Y");
				break;
			case self::T_BOOLEAN:
				return $this->value ? 'true' : 'false';
				break;
			case self::T_FUNCTION:
				return $this->value;
				break;
			default:
				return (string)$this->value;
		}
	}
}

class Expression {

	protected $tokens = array( );
	protected $postfixed = false;
	
	public function get(){
		return $this->tokens;
	}
	
	public function push(Token $t){
		$this->tokens[] = $t;
	}
	
	public function pop(){
		return array_pop($this->tokens);
	}
	
	public function peek(){
		return end($this->tokens);
	}
	
	public function postfix () {
		$stack = array();
		$rpn = array();
		
		foreach ($this->tokens as $token) {
			switch ($token->type) {
				case Token::T_COMMA:
					while (!empty($stack) && end($stack)->type != Token::T_POPEN) {
						$rpn[] = array_pop($stack);
					}
					break;
				case Token::T_NUMBER:
				case Token::T_DATE:
				case Token::T_BOOLEAN:
				case Token::T_TEXT:
				case Token::T_IDENT:
				case Token::T_FIELD:
				case Token::T_UNDEFINED:
					$rpn[] = $token;
					break;
				case Token::T_PCLOSE:
					while (!empty($stack) && end($stack)->type != Token::T_POPEN) {
						$rpn[] = array_pop($stack);
					}
					if (empty($stack)) {
						throw new \Exception("Closing parenthesis without opening parenthesis ");
					}
					array_pop($stack);
					if (!empty($stack)
						&& end($stack)->type == Token::T_FUNCTION) {
						$rpn[] = array_pop($stack);
					}
					break;
				case Token::T_POPEN:
				case Token::T_FUNCTION:
					$stack[] = $token;
					break;
				default:
					if ($token->isOperator() || $token->isComparator()) {
						while (!empty($stack)
							&& (end($stack)->isOperator() || end($stack)->isComparator())
							&& (($token->associativity() == Token::A_LEFT && $token->precedence() >= end($stack)->precedence()) || ($token->associativity() == Token::A_RIGHT && $token->precedence() > end($stack)->precedence()))) {
							$rpn[] = array_pop($stack);
						}
						$stack[] = $token;
					} else {
						throw new \Exception("Unrecognized token " . $token->value);
					}
					break;
			}
		}
		while (!empty($stack) && end($stack)->type != Token::T_POPEN) {
			$rpn[] = array_pop($stack);
		}
		if (!empty($stack)) {
			throw new \Exception("Opening parenthesis without closing parenthesis ");
		}
		$this->tokens = $rpn;
		$this->postfixed = true;
	}
	
	public function setFields($fields) {
		foreach ($this->tokens as $token) {
			if ($token->type == Token::T_FIELD && count($fields) >= $token->value) {
				$value = $fields[$token->value - 1];
				if (is_numeric($value)) {
					$token->type = Token::T_NUMBER;
					$token->value = $value;
				} else if (preg_match("/^\d{1,2}\/\d{1,2}\/\d{4}$/", $value)) {
                	$token->type = Token::T_DATE;
					$date = \DateTime::createFromFormat("d/m/Y", $value, new \DateTimeZone( 'Europe/Paris' ));
					$error = \DateTime::getLastErrors();
					if ($error['error_count'] > 0) {
						throw new \Exception($error['errors'][0]);
					}
					$date->setTime(0, 0, 0);
					$token->value = $date;
				} elseif (in_array($value, array('true', 'false'))) {
					$token->type = Token::T_BOOLEAN;
					$token->value = $value == 'true';
				} else {
					$token->type = Token::T_TEXT;
					$token->value = $value;
				}
			}
		}
	}
	
	public function setNamedFields($fields) {
		foreach ($this->tokens as $token) {
			if ($token->type == Token::T_IDENT && isset($fields[$token->value])) {
				$value = $fields[$token->value];
				if (is_numeric($value)) {
					$token->type = Token::T_NUMBER;
					$token->value = $value;
				} else if (preg_match("/^\d{1,2}\/\d{1,2}\/\d{4}$/", $value)) {
                	$token->type = Token::T_DATE;
					$date = \DateTime::createFromFormat("d/m/Y", $value, new \DateTimeZone( 'Europe/Paris' ));
					$error = \DateTime::getLastErrors();
					if ($error['error_count'] > 0) {
						throw new \Exception($error['errors'][0]);
					}
					$date->setTime(0, 0, 0);
					$token->value = $date;
				} elseif (in_array($value, array('true', 'false'))) {
					$token->type = Token::T_BOOLEAN;
					$token->value = $value == 'true';
				} else {
					$token->type = Token::T_TEXT;
					$token->value = $value;
				}
			}
		}
	}
	
	public function setVariables($variables) {
		$completed = true;
		foreach ($this->tokens as $token) {
			if ($token->type == Token::T_FIELD && isset($variables[''.$token->value])) {
				$value = $variables[''.$token->value];
				if ($value == "") {
					$completed = false;
				} else if (is_numeric($value)) {
					$token->type = Token::T_NUMBER;
					$token->value = $value;
				} else if (preg_match("/^\d{1,2}\/\d{1,2}\/\d{4}$/", $value)) {
                	$token->type = Token::T_DATE;
					$date = \DateTime::createFromFormat("d/m/Y", $value, new \DateTimeZone( 'Europe/Paris' ));
					$error = \DateTime::getLastErrors();
					if ($error['error_count'] > 0) {
						throw new \Exception($error['errors'][0]);
					}
					$date->setTime(0, 0, 0);
					$token->value = $date;
				} elseif (in_array($value, array('true', 'false'))) {
					$token->type = Token::T_BOOLEAN;
					$token->value = $value == 'true';
				} else {
					$token->type = Token::T_TEXT;
					$token->value = $value;
				}
			} else if ($token->type == Token::T_IDENT && isset($variables[$token->value])) {
				$value = $variables[$token->value];
				if ($value == "") {
					$completed = false;
				} else if (is_numeric($value)) {
					$token->type = Token::T_NUMBER;
					$token->value = $value;
				} else if (preg_match("/^\d{1,2}\/\d{1,2}\/\d{4}$/", $value)) {
                	$token->type = Token::T_DATE;
					$date = \DateTime::createFromFormat("d/m/Y", $value, new \DateTimeZone( 'Europe/Paris' ));
					$error = \DateTime::getLastErrors();
					if ($error['error_count'] > 0) {
						throw new \Exception($error['errors'][0]);
					}
					$date->setTime(0, 0, 0);
					$token->value = $date;
				} elseif (in_array($value, array('true', 'false'))) {
					$token->type = Token::T_BOOLEAN;
					$token->value = $value == 'true';
				} else {
					$token->type = Token::T_TEXT;
					$token->value = $value;
				}
			} elseif ($token->type == Token::T_FIELD || $token->type == Token::T_IDENT)  {
				$completed = false;
			}
		}
		return $completed;
	}
	
	public function evaluate() {
		try {
			$ops = array();
			foreach ($this->tokens as $token) {
				if ($token->isOperator()) {
					$ops[] = $this->operation($token, $ops);
				} elseif ($token->isComparator()) {
					$ops[] = $this->comparison($token, $ops);
				} else {
					switch ($token->type) {
						case Token::T_NUMBER:
						case Token::T_DATE:
						case Token::T_BOOLEAN:
						case Token::T_TEXT:
						case Token::T_IDENT:
						case Token::T_FIELD:
						case Token::T_UNDEFINED:
							$ops[] = $token;
							break;
						case Token::T_FUNCTION:
							$ops[] = $this->func($token, $ops);
							break;
						default:
							throw new \Exception("Unrecognized token " . $token->value);
					}
				}
			}
			$result = end($ops);
			return $result->isVariable() ? false : ''.$result;
		} catch (\Exception $e) {
			return false;
		}
	}
	
	private function operation(Token $op, &$args) {
		if ($op->isUnaryOperator()) {
			if (count($args) < 1) {
				throw new \Exception("Illegal number (".count($args).") of operands for " . $op);
			}
			$arg1 = array_pop($args);
		} else if ($op->isBinaryOperator()) {
			if (count($args) < 2) {
				throw new \Exception("Illegal number (".count($args).") of operands for " . $op);
			}
			$arg2 = array_pop($args);
			$arg1 = array_pop($args);
		} else if ($op->isTernaryOperator()) {
			if (count($args) < 3) {
				throw new \Exception("Illegal number (".count($args).") of operands for " . $op);
			}
			$arg3 = array_pop($args);
			$arg2 = array_pop($args);
			$arg1 = array_pop($args);
		}
		$result = new Token(Token::T_NUMBER, 0);
		switch ($op->type) {
			case Token::T_PLUS:
				if ($arg1->isVariable() || $arg2->isVariable()) {
					$result->type = Token::T_UNDEFINED;
					$result->value = array($arg1, $arg2);
				} elseif ($arg1->type == Token::T_NUMBER) { 
					if ($arg2->type == Token::T_NUMBER) {
						$result->value = $arg1->value + $arg2->value;
					} else if ($arg2->type == Token::T_DATE) {
						$date = $arg2->value;
						$date->add(new \DateInterval('P'.$arg1->value.'D'));
						$result->type = Token::T_DATE;
						$result->value = $date;
					} else if ($arg2->type == Token::T_TEXT) {
						$result->type = Token::T_TEXT;
						$result->value = (string)$arg1->value.$arg2->value;
					} else {
						throw new \Exception("Illegal argument '".$arg2."' for ".$op);
					}
				} else if ($arg1->type == Token::T_DATE) {
					if ($arg2->type == Token::T_NUMBER) {
						$date = $arg1->value;
						$date->add(new \DateInterval('P'.$arg2->value.'D'));
						$result->type = Token::T_DATE;
						$result->value = $date;
					} else if ($arg2->type == Token::T_TEXT) {
						$result->type = Token::T_TEXT;
						$result->value = $arg1->value->format("d/m/Y").$arg2->value;
					} else {
						throw new \Exception("Illegal argument '".$arg2."' for ".$op);
					}
				} else if ($arg1->type == Token::T_TEXT) {
					$result->type = Token::T_TEXT;
					if ($arg2->type == Token::T_NUMBER) {
						$result->value = $arg1->value.(string)$arg2->value;
					} else if ($arg2->type == Token::T_DATE) {
						$result->value = $arg1->value.$arg2->value->format("d/m/Y");
					} else if ($arg2->type == Token::T_TEXT) {
						$result->value = $arg1->value.$arg2->value;
					} else {
						throw new \Exception("Illegal argument '".$arg2."' for ".$op);
					}
				} else {
					throw new \Exception("Illegal argument '".$arg1."' for ".$op);
				}
				break;
			case Token::T_MINUS:
				if ($arg1->isVariable() || $arg2->isVariable()) {
					$result->type = Token::T_UNDEFINED;
					$result->value = array($arg1, $arg2);
				} elseif ($arg1->type == Token::T_NUMBER) { 
					if ($arg2->type == Token::T_NUMBER) {
						$result->value = $arg1->value - $arg2->value;
					} else {
						throw new \Exception("Illegal argument '".$arg2."' for ".$op);
					}
				} else if ($arg1->type == Token::T_DATE) {
					if ($arg2->type == Token::T_NUMBER) {
						$date = $arg1->value;
						$ivl = new \DateInterval('P'.$arg2->value.'D');
						$ivl->invert = 1;
						$date->add($ivl);
						$result->type = Token::T_DATE;
						$result->value = $date;
					} else if ($arg2->type == Token::T_DATE) {
						$result->value = ($arg1->value > $arg2->value)
							? $arg1->value->diff($arg2->value)->days
							: 0;
					} else {
						throw new \Exception("Illegal argument '".$arg2."' for ".$op);
					}
				} else {
					throw new \Exception("Illegal argument '".$arg1."' for ".$op);
				}
				break;
			case Token::T_TIMES:
				if ($arg1->isVariable() || $arg2->isVariable()) {
					$result->type = Token::T_UNDEFINED;
					$result->value = array($arg1, $arg2);
				} elseif ($arg1->type != Token::T_NUMBER || $arg2->type != Token::T_NUMBER) { 
					throw new \Exception("Illegal argument '".$arg2."' : operands must be numbers for ".$op);
				} else {
					$result->value = $arg1->value * $arg2->value;
				}
				break;
			case Token::T_DIV:
				if ($arg1->isVariable() || $arg2->isVariable()) {
					$result->type = Token::T_UNDEFINED;
					$result->value = array($arg1, $arg2);
				} elseif ($arg1->type != Token::T_NUMBER || $arg2->type != Token::T_NUMBER) { 
					throw new \Exception("Illegal argument : operands must be numbers for ".$op);
				} else {
					$result->value = (float)$arg1->value / $arg2->value;
				}
				break;
			case Token::T_MOD:
				if ($arg1->isVariable() || $arg2->isVariable()) {
					$result->type = Token::T_UNDEFINED;
					$result->value = array($arg1, $arg2);
				} elseif ($arg1->type != Token::T_NUMBER || $arg2->type != Token::T_NUMBER) { 
					throw new \Exception("Illegal argument : operands must be numbers for ".$op);
				} else {
					$result->value = (float)$arg1->value % $arg2->value;
				}
				break;
			case Token::T_POW:
				if ($arg1->isVariable() || $arg2->isVariable()) {
					$result->type = Token::T_UNDEFINED;
					$result->value = array($arg1, $arg2);
				} elseif ($arg1->type != Token::T_NUMBER || $arg2->type != Token::T_NUMBER) { 
					throw new \Exception("Illegal argument : operands must be numbers for ".$op);
				} else {
					$result->value = (float)pow($arg1->value, $arg2->value);
				}
				break;
			case Token::T_BITWISE_AND:
				if ($arg1->isVariable() || $arg2->isVariable()) {
					$result->type = Token::T_UNDEFINED;
					$result->value = array($arg1, $arg2);
				} elseif ($arg1->type != Token::T_NUMBER || $arg2->type != Token::T_NUMBER) { 
					throw new \Exception("Illegal argument : operands must be numbers for ".$op);
				} else {
					$result->value = (float)$arg1->value & $arg2->value;
				}
				break;
			case Token::T_BITWISE_XOR:
				if ($arg1->isVariable() || $arg2->isVariable()) {
					$result->type = Token::T_UNDEFINED;
					$result->value = array($arg1, $arg2);
				} elseif ($arg1->type != Token::T_NUMBER || $arg2->type != Token::T_NUMBER) { 
					throw new \Exception("Illegal argument : operands must be numbers for ".$op);
				} else {
					$result->value = (float)$arg1->value ^ $arg2->value;
				}
				break;
			case Token::T_BITWISE_OR:
				if ($arg1->isVariable() || $arg2->isVariable()) {
					$result->type = Token::T_UNDEFINED;
					$result->value = array($arg1, $arg2);
				} elseif ($arg1->type != Token::T_NUMBER || $arg2->type != Token::T_NUMBER) { 
					throw new \Exception("Illegal argument : operands must be numbers for ".$op);
				} else {
					$result->value = (float)$arg1->value | $arg2->value;
				}
				break;
			case Token::T_LOGICAL_AND:
				$result->type = Token::T_BOOLEAN;
				if ($arg1->type == Token::T_BOOLEAN && $arg2->type == Token::T_BOOLEAN) {
					$result->value = $arg1->value && $arg2->value;
				} elseif ($arg1->type == Token::T_BOOLEAN) {
					if (! $arg1->value) {
						$result->value = false;
					} elseif ($arg2->isVariable()) {
						$result->type = Token::T_UNDEFINED;
						$result->value = array($arg1, $arg2);
					} else {
						throw new \Exception("Illegal argument 2 : operand must be boolean for ".$op);
					}
				} elseif ($arg2->type == Token::T_BOOLEAN) {
					if (! $arg2->value) {
						$result->value = false;
					} elseif ($arg1->isVariable()) {
						$result->type = Token::T_UNDEFINED;
						$result->value = array($arg1, $arg2);
					} else {
						throw new \Exception("Illegal argument 1 : operand must be boolean for ".$op);
					}
				} elseif ($arg1->isVariable() || $arg2->isVariable()) {
					$result->type = Token::T_UNDEFINED;
					$result->value = array($arg1, $arg2);
				} else {
					throw new \Exception("Illegal argument : operands must be boolean for ".$op);
				}
				break;
			case Token::T_LOGICAL_OR:
				$result->type = Token::T_BOOLEAN;
				if ($arg1->type == Token::T_BOOLEAN && $arg2->type == Token::T_BOOLEAN) {
					$result->value = $arg1->value || $arg2->value;
				} elseif ($arg1->type == Token::T_BOOLEAN) {
					if ($arg1->value) {
						$result->value = true;
					} elseif ($arg2->isVariable()) {
						$result->type = Token::T_UNDEFINED;
						$result->value = array($arg1, $arg2);
					} else {
						throw new \Exception("Illegal argument 2 : operand must be boolean for ".$op);
					}
				} elseif ($arg2->type == Token::T_BOOLEAN) {
					if ($arg2->value) {
						$result->value = true;
					} elseif ($arg1->isVariable()) {
						$result->type = Token::T_UNDEFINED;
						$result->value = array($arg1, $arg2);
					} else {
						throw new \Exception("Illegal argument 1 : operand must be boolean for ".$op);
					}
				} elseif ($arg1->isVariable() || $arg2->isVariable()) {
					$result->type = Token::T_UNDEFINED;
					$result->value = array($arg1, $arg2);
				} else {
					throw new \Exception("Illegal argument : operands must be boolean for ".$op);
				}
				break;
			case Token::T_UNARY_PLUS:
				if ($arg1->isVariable()) {
					$result->type = Token::T_UNDEFINED;
					$result->value = array($arg1);
				} elseif ($arg1->type != Token::T_NUMBER) { 
					throw new \Exception("Illegal argument '".$arg1."' : operand must be a number for ".$op);
				} else {
					$result->value = $arg1->value;
				}
				break;
			case Token::T_UNARY_MINUS:
				if ($arg1->isVariable()) {
					$result->type = Token::T_UNDEFINED;
					$result->value = array($arg1);
				} elseif ($arg1->type != Token::T_NUMBER) { 
					throw new \Exception("Illegal argument '".$arg1."' : operand must be a number for ".$op);
				} else {
					$result->value = -$arg1->value;
				}
				break;
			case Token::T_NOT:
				if ($arg1->isVariable()) {
					$result->type = Token::T_UNDEFINED;
					$result->value = array($arg1);
				} elseif ($arg1->type != Token::T_NUMBER && $arg1->type != Token::T_BOOLEAN) { 
					throw new \Exception("Illegal argument '".$arg1."' : operand must be a number or a boolean for ".$op);
				} else {
					$result->type = $arg1->type;
					$result->value = !$arg1->value;
				}
				break;
			case Token::T_DEGRE:
				if ($arg1->isVariable()) {
					$result->type = Token::T_UNDEFINED;
					$result->value = array($arg1);
				} elseif ($arg1->type != Token::T_NUMBER) { 
					throw new \Exception("Illegal argument '".$arg1."' : operand must be a number for ".$op);
				} else {
					$result->value = deg2rad($arg1->value);
				}
				break;
			case Token::T_TERNARY_ELSE:
				$result = $arg1;
				break;
			case Token::T_TERNARY:
				if ($arg1->isVariable()) {
					$result->type = Token::T_UNDEFINED;
					$result->value = array($arg1, $arg2, $arg3);
				} elseif ($arg1->type != Token::T_BOOLEAN) { 
					throw new \Exception("Illegal argument '".$arg1."' : operand 1 must be a condition for ".$op);
				} else {
					$result = $arg1->value ? $arg2 : $arg3;
				}
				break;
		}
		return $result;
	}
	
	private function comparison(Token $op, &$args) {
		if (count($args) < 2) {
			throw new \Exception("Illegal number (".count($args).") of operands for " . $op);
		}
		$arg2 = array_pop($args);
		$arg1 = array_pop($args);
		if ($arg1->isVariable() || $arg2->isVariable()) {
			$result = new Token(Token::T_UNDEFINED, array($arg1, $arg2));
		} elseif ($arg1->type != $arg2->type) { 
			throw new \Exception("operand types for '" . $op. "' are not identical");
		} else {
			$result = new Token(Token::T_BOOLEAN, false);
			switch ($op->type) {
				case Token::T_EQUAL:
					$result->value = ($arg1->value == $arg2->value);
					break;
				case Token::T_NOT_EQUAL:
					$result->value = ($arg1->value != $arg2->value);
					break;
				case Token::T_LESS_THAN:
					$result->value = ($arg1->value < $arg2->value);
					break;
				case Token::T_LESS_OR_EQUAL:
					$result->value = ($arg1->value <= $arg2->value);
					break;
				case Token::T_GREATER_THAN:
					$result->value = ($arg1->value > $arg2->value);
					break;
				case Token::T_GREATER_OR_EQUAL:
					$result->value = ($arg1->value >= $arg2->value);
					break;
			}
		}
		return $result;
	}
	
	private function func(Token $func, &$args) {
		$functions = array(
			"abs" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return abs($a); }),
			"acos" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return acos($a); }),
			"acosh" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return acosh($a); }),
			"asin" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return asin($a); }),
			"asinh" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return asinh($a); }),
			"atan" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return atan($a); }),
			"atan2" => array(2, array(Token::T_NUMBER, Token::T_NUMBER), Token::T_NUMBER, function($a, $b) { return atan2($a, $b); }),
			"atanh" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return atanh($a); }),
			"ceil" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return ceil($a); }),
			"cos" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return cos($a); }),
			"cosh" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return cosh($a); }),
			"day" => array(1, array(Token::T_DATE), Token::T_NUMBER, function($a) { return (float)$a->format('d'); }),
			"exp" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return exp($a); }),
			"floor" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return floor($a); }),
			"fullmonth" => array(1, array(Token::T_DATE), Token::T_TEXT, function($a) {
				$months = array("janvier", "février", "mars", "avril", "mai", "juin",  "juillet", "août", "septembre", "octobre", "novembre", "décembre");
				return $months[(int)$a->format('m') - 1].' '.$a->format('Y');
			}),
			"log" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return log($a); }),
			"log10" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return log10($a); }),
			"max" => array(2, array(Token::T_NUMBER, Token::T_NUMBER), Token::T_NUMBER, function($a, $b) { return max($a, $b); }),
			"min" => array(2, array(Token::T_NUMBER, Token::T_NUMBER), Token::T_NUMBER, function($a, $b) { return min($a, $b); }),
			"month" => array(1, array(Token::T_DATE), Token::T_NUMBER, function($a) { return (float)$a->format('m'); }),
			"pow" => array(2, array(Token::T_NUMBER, Token::T_NUMBER), Token::T_NUMBER, function($a, $b) { return pow($a, $b); }),
			"rand" => array(0, array(), Token::T_NUMBER, function() { return rand(); }),
			"round" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return round($a); }),
			"sin" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return sin($a); }),
			"sinh" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return sinh($a); }),
			"sqrt" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return sqrt($a); }),
			"tan" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return tan($a); }),
			"tanh" => array(1, array(Token::T_NUMBER), Token::T_NUMBER, function($a) { return tanh($a); }),
			"year" => array(1, array(Token::T_DATE), Token::T_NUMBER, function($a) { return (float)$a->format('Y'); })
		);
		if ($func->value == "defined") {
			if (count($args) < 1) { 
				throw new \Exception("Illegal number (".count($args).") of operands for function" . $func);
			}
			$arg = array_pop($args);
			if ($arg->isVariable()) {
				return new Token(Token::T_BOOLEAN, false);
			}
			if ($arg->value === null || $arg->value == "") {
				return new Token(Token::T_BOOLEAN, false);
			}
			return new Token(Token::T_BOOLEAN, true);
		}
		if (! isset($functions[$func->value])) {
			throw new \Exception("Unknown function : " . $func);
		}
		$argc = $functions[$func->value][0];
		if (count($args) < $argc) {
			throw new \Exception("Illegal number (".count($args).") of operands for function" . $func);
		}
		$argv = array();
		for (; $argc > 0; --$argc) {
			$arg = array_pop($args);
			if ($arg->isVariable()) {
				return new Token(Token::T_UNDEFINED, array($arg));
			}
			$type = $functions[$func->value][1][$argc - 1];
			if ($arg->type != $type) { 
				$expected = "";
				switch ($type) {
					case Token::T_NUMBER:
						$expected = "number";
						break;
					case Token::T_DATE: 
						$expected = "date";
						break;
					case Token::T_BOOLEAN:
						$expected = "boolean";
						break;
					case Token::T_TEXT: 
						$expected = "text";
						break;
				}
				throw new \Exception("Illegal type for argument '".$arg."' : operand must be a ".$expected." for ".$func);
			}
			array_unshift($argv, $arg->value); 
		}
		return new Token($functions[$func->value][2], call_user_func_array($functions[$func->value][3], $argv));
	}
	
}

class ExpressionParser {

	const PATTERN = '/([\s!,\+\-\*\/\^%\(\)=\<\>\&\^\|\?\:°])/u';

    protected $lookup = array(
        '+' => Token::T_PLUS,
        '-' => Token::T_MINUS,
        '/' => Token::T_DIV,
        '%' => Token::T_MOD,
        '(' => Token::T_POPEN,
        ')' => Token::T_PCLOSE,
        '*' => Token::T_TIMES,
        '!' => Token::T_NOT,
        ',' => Token::T_COMMA,
        '=' => Token::T_EQUAL,
        '<' => Token::T_LESS_THAN,
        '>' => Token::T_GREATER_THAN,
        '&' => Token::T_BITWISE_AND,
        '^' => Token::T_BITWISE_XOR,
        '|' => Token::T_BITWISE_OR,
        '?' => Token::T_TERNARY,
        ':' => Token::T_TERNARY_ELSE,
        '°' => Token::T_DEGRE
    );
	
	private $text = array();

	private function replaceText($matches) {
		$this->text[] = substr($matches[0], 1, strlen($matches[0]) - 2);
		return "¤".count($this->text);
	}
	
	public function parse ($infix) {
		$constants = array(
			'pi'	=> new Token(Token::T_NUMBER, M_PI),
			'now'	=> new Token(Token::T_DATE, new \DateTime()),
			'today'	=> new Token(Token::T_DATE, new \DateTime()),
			'true'	=> new Token(Token::T_BOOLEAN, true),
			'false'	=> new Token(Token::T_BOOLEAN, false)
		);
		$expr = new Expression();
		$infix = preg_replace_callback(
			array("|'[^']*'|", '|"[^"]*"|'),
			array($this, 'replaceText'),
			$infix
		);
		$infix = preg_replace("#(\d{1,2})/(\d{1,2})/(\d{4})#", "D$1.$2.$3", $infix);
		$toks = preg_split(self::PATTERN, $infix, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$prev = new Token(Token::T_NOOP, 'noop');
		foreach ($toks as $value) {
			$value = trim($value);
			if (is_numeric($value)) {
                if ($prev->type === Token::T_PCLOSE)
                    $expr->push(new Token(Token::T_TIMES, '*'));
                $expr->push($prev = new Token(Token::T_NUMBER, (float) $value));
            } else if (preg_match("/^#\d+$/", $value)) {
                if ($prev->type === Token::T_PCLOSE)
                    $expr->push(new Token(Token::T_TIMES, '*'));
                $expr->push($prev = new Token(Token::T_FIELD, (int)substr($value, 1)));
            } else if (preg_match("/^¤(\d+)$/", $value, $matches)) {
                if ($prev->type === Token::T_PCLOSE)
                    $expr->push(new Token(Token::T_TIMES, '*'));
				$i = (int)$matches[1];
                $expr->push($prev = new Token(Token::T_TEXT, $this->text[$i - 1]));
            } else if (preg_match("/^D(\d{1,2})\.(\d{1,2})\.(\d{4})$/", $value, $matches)) {
                if ($prev->type === Token::T_PCLOSE)
                    $expr->push(new Token(Token::T_TIMES, '*'));
				$date = \DateTime::createFromFormat("d/m/Y", $matches[1]."/".$matches[2]."/".$matches[3], new \DateTimeZone( 'Europe/Paris' ));
				$error = \DateTime::getLastErrors();
				if ($error['error_count'] > 0) {
					throw new \Exception($error['errors'][0]);
				}
				$date->setTime(0, 0, 0);
                $expr->push($prev = new Token(Token::T_DATE, $date));
			} elseif (isset($constants[$value])) {
                if ($prev->type === Token::T_PCLOSE)
                    $expr->push(new Token(Token::T_TIMES, '*'));
                $expr->push($prev = clone $constants[$value]);
			} else if ($value != "") {
				switch ($type = isset($this->lookup[$value]) ? $this->lookup[$value] : Token::T_IDENT) {
					case Token::T_EQUAL:
						switch ($prev->type) {
							case Token::T_NOT:
								$expr->pop();
								$type = Token::T_NOT_EQUAL;
								$value = "!=";
								break;
							case Token::T_LESS_THAN:
								$expr->pop();
								$type = Token::T_LESS_OR_EQUAL;
								$value = "<=";
								break;
							case Token::T_GREATER_THAN:
								$expr->pop();
								$type = Token::T_GREATER_OR_EQUAL;
								$value = ">=";
								break;
						}						
						break;
					case Token::T_BITWISE_AND:
						if ($prev->type === Token::T_BITWISE_AND) {
							$expr->pop();
							$type = Token::T_LOGICAL_AND;
							$value = "&&";
						}
						break;
					case Token::T_BITWISE_OR:
						if ($prev->type === Token::T_BITWISE_OR) {
							$expr->pop();
							$type = Token::T_LOGICAL_OR;
							$value = "||";
						}
						break;
					case Token::T_TIMES:
						if ($prev->type === Token::T_TIMES) {
							$expr->pop();
							$type = Token::T_POW;
							$value = "**";
						}
						break;
					case Token::T_PLUS:
						if ($prev->isOperator() || $prev->isComparator() || $prev->isBeforeFunctionArgument())
							$type = Token::T_UNARY_PLUS;
						break;

					case Token::T_MINUS:
						if ($prev->isOperator() || $prev->isComparator() || $prev->isBeforeFunctionArgument())
							$type = Token::T_UNARY_MINUS;
						break;

					case Token::T_POPEN:
						switch ($prev->type) {
							case Token::T_IDENT:
								$prev->type = Token::T_FUNCTION;
								break;

							case Token::T_NUMBER:
							case Token::T_DATE:
							case Token::T_BOOLEAN:
							case Token::T_TEXT:
							case Token::T_PCLOSE:
								$expr->push(new Token(Token::T_TIMES, '*'));
								break;
						}

						break;
				}
				$expr->push($prev = new Token($type, $value));
			}
		}
		return $expr;	
	}
}

$cli = false;
if ($cli) {
	try {
		// $infix = "3 + #1 * 2 /  (1 - 5 ) ** 2 ** 3";
		// $infix = "!(dateInitiale < now && 5° < #1)";
		// $infix = "1 + (2 > 1 ? 2 : (4 > 10 ? 12 : 5 + 10)) * 2";
		// $infix = '#1 >= 01/01/2005 ? \'tout a fait vrai\' : "tout a fait faux"';
		$infix = "((#1 == 0) ? ((#2 == 1) ? 18.0 : ((#2 == 2) ? 15.5 : ((#2 == 3) ? 13.3 : ((#2 == 4) ? 11.7 : ((#2 == 5) ? 10.6 : 9.5))))) : ((#1 == 1) ? ((#2 == 1) ? 13.5 : ((#2 == 2) ? 11.5 : ((#2 == 3) ? 10.0 : ((#2 == 4) ? 8.8 : ((#2 == 5) ? 8.0 : 7.2)))))) : ((#2 == 1) ? 9.0 : ((#2 == 2) ? 7.8 : ((#2 == 3) ? 6.7 : ((#2 == 4) ? 5.9 : ((#2 == 5) ? 5.3 : 4.8))))))";
		$parser = new ExpressionParser();
		$expr = $parser->parse($infix);
		$expr->postfix();
		// $expr->setFields(array(4));
		$expr->setVariables(array('1' => 0, '2' => 2));
		$expr->setNamedFields(array('dateInitiale'=>'17/10/2014'));
		foreach ($expr->get() as $token) {
			echo "[".$token . "]\n";
		}
		echo $expr->evaluate();
	} catch (\Exception $e) {
		echo $e->getMessage();
	}
}
?>