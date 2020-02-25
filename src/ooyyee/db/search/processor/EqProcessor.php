<?php

namespace ooyyee\db\search\processor;

/**
 * 相等
 *
 * @author lpdx111
 *        
 */
class EqProcessor extends Processor {
	
	/**
	 * @param array options=[value,filter]
	 * @see \ooyyee\db\search\processor\Processor::run()
	 */
	public function run( $options) {
		$valueKey=$options[0];
		$filter=$options[1]??null;
		if ($this->isExists ( $valueKey )) {
			return  $this->filter($this->search [$valueKey],$filter);
		}
		return false;
	}
}

?>