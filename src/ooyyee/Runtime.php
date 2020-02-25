<?php

namespace ooyyee;

use think\facade\Db;

class Runtime
{
	public static function get($key,$defaultValue=''){
	   $db=Db::connect('core')->name('runtime');
		$value=$db->where('key',$key)->value('value');
		if($value){
            $value=json_decode($value,true);
            return $value['data']??$defaultValue;
		}
		return $defaultValue;
	}
	public static function save($key,$value):void {
        $db=Db::connect('core')->name('runtime');
        $db->insert(['key'=>$key,'value'=>json_encode(['data'=>$value])],true);
	}
}
