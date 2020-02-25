<?php

namespace ooyyee\db\search\processor;
/**
 * time
 * @author lpdx111
 *
 */
class TimeProcessor extends Processor {
	
	/**
	 * @param array $options [timeRange]
	 * @see \ooyyee\db\search\processor\Processor::run()
	 */
	public function run($options) {
		$valueKey=$options[0];
		if($this->isExists($valueKey)){
			$timeRange=$this->search [$valueKey];
			[$min, $max] = explode ( ' - ', $timeRange );
			$min = strtotime ( $min );
			$max = strtotime ( $max . ' 23:59:59' );
			return [
					'between',
					[
							$min,
							$max
					]
			];
		}
		return false;
	}
}

?>