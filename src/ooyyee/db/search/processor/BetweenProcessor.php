<?php

namespace ooyyee\db\search\processor;
/**
 * between
 * @author lpdx111
 *
 */
class BetweenProcessor extends Processor {
	
	/**
	 * @param array $options ==[start_time,end_time,filter]
	 * @see \ooyyee\db\search\processor\Processor::run()
	 */
	public function run($options) {
	    [$valueKey1,$valueKey2]=$options;
		$filter=$options[2]??null;
		if($this->isExists($valueKey1)&&$this->isExists($valueKey2)){
			$value1=$this->filter($this->search[$valueKey1],$filter);
			$value2=$this->filter($this->search[$valueKey2],$filter);
			return ['between',[$value1,$value2]];
		}
		if($this->isExists($valueKey1)){
			$value1=$this->filter($this->search[$valueKey1],$filter);
			return['egt',$value1];
		}
		if($this->isExists($valueKey2)){
			$value2=$this->filter($this->search[$valueKey2],$filter);
			return['elt',$value2];
		}
		return false;
	}
}

