<?php

namespace ooyyee\db\search;

class Base64Search {
	private $name;
	private $type;
	public function __construct($name='advSearch',$type='formSerialize'){
		$this->name=$name;
		$this->type=$type;
	}
	
	public function decode(){
		$data=base64_decode(request()->param($this->name));
		switch ($this->type){
			case 'json':
				return json_decode($data,true);
			case 'formSerialize':
				$serialize=array();
				parse_str($data,$serialize);
				return $serialize;
			case 'serialize':
				return unserialize($data);
			default:
				return $data;
		}

	}
}
