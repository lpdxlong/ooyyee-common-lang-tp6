<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-10-16
 * Time: 11:00
 */

namespace ooyyee\utils;

use think\Request;

class StaticResource
{
    public static function image($url){
        if(strpos($url,"public://") ===0){
            return str_replace('public://',\think\facade\Request::domain().'/',$url);
        }else if(strpos($url,"qiniu://") ===0){
            return str_replace('qiniu://','http://ooyyee-static.qiniu.ooyyee.com/',$url);
        }
        return $url;
    }
}