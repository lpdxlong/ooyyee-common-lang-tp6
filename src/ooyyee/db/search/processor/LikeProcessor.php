<?php

namespace ooyyee\db\search\processor;

class LikeProcessor extends Processor {
	
	/**
	 * @param $options [value,likeType,filter]
	 * @see \ooyyee\db\search\processor\Processor::run()
	 */
	public function run($options) {
		$valueKey=$options[0];
		if($this->isExists($valueKey)){
			$valueType = $options[1] ?? 'all';
			$filter=$options[2]??null;
			$value=$this->filter($this->search[$valueKey],$filter);
			switch ($valueType){
				case 'all':
					$value='%'.$value.'%';
					break;
				case 'left':
					$value='%'.$value;
					break;
				case 'right':
					$value.='%';
					break;
			}
			return ['like',$value];
		}
		return false;
		
	}
}

?>