<?php

namespace ooyyee\db\search\processor;

/**
 * function
 * 
 * @author lpdx111
 *        
 */
class FunctionProcessor extends Processor {
	
	/**
	 * @param  array $options =[value,function(){}]
	 * 
	 * @see \ooyyee\db\search\processor\Processor::run()
	 */
	public function run($options) {
		$valueKey = $options[0];
		if (is_string ( $valueKey )) {  // 单独的string 值
			$function = $options[1];
			if ($this->isExists ( $valueKey )) {
				$value = $this->search[$valueKey];
				if (is_callable ( $function )) {
					$result = $function($value);
					if ($result !== false) {
						$buildOptions = null;
						if (is_array ( $result )) {
							$buildOptions = $result;
						} else {
							$buildOptions = [ 'eq',$result ];
						}
						return $buildOptions;
					}
				}
			}
		} else if (is_array ( $valueKey )) {  // 多个值
			$values = [ ];
			foreach ( $valueKey as $key ) {
				if ($this->isExists ( $key )) {
					$value = $this->search[$key];
					$values[$key] = $value;
				}
			}

			if (! empty ( $values )) {
                $function = $options[1];
				if (is_callable ( $function )) {
                    $result = $function($values);
					if ($result !== false) {
						$buildOptions = null;
						if (is_array ( $result )) {
							$buildOptions = $result;
						} else {
							$buildOptions = [ 'eq',$result ];
						}
						return $buildOptions;
					}
				}
			}
		}
		return false;
	}
}

?>