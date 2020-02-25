<?php

namespace ooyyee\db\search;

class FunctionHandler implements IFunctionHandler
{
	public  $field;
	public function __construct($field){
		$this->field=$field;
	}

    /**
     * @param $value
     * @return bool|array
     */
	public function run($value){
	    return false;
    }
	
	
	public static function define($field):array {
		return ['class',new static($field)];
	}
}

