<?php

namespace ooyyee\db\search\processor;

use ooyyee\db\search\FunctionHandler;

class ClassProcessor extends Processor
{
    /**
     * @param array $options
     * @return bool|array
     */
	public function run($options) {
		$handler=$options[0];
		if($handler instanceof FunctionHandler){
			$valueKey=$handler->field;
			if ($this->isExists ( $valueKey )) {
				$value=$this->search [$valueKey];
				return $handler->run($value);
			}
		}
		return false;
	}
}

