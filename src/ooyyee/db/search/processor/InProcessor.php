<?php

namespace ooyyee\db\search\processor;

class InProcessor extends Processor {
	
	/**
	 * @param array $options=[values,filter]
	 * @see \ooyyee\db\search\processor\Processor::run()
	 */
	public function run($options) {
		$valueKey=$options[0];
		$filter=$options[1]??null;
		if($this->isExists($valueKey)){
			return ['in',$this->filter($this->search[$valueKey],$filter)];
		}
		return false;
	}
}

?>