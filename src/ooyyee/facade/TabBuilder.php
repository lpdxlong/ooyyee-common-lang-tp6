<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-12-26
 * Time: 10:23
 */

namespace ooyyee\facade;


use think\Facade;
/**
 * @see \ooyyee\ui\TabBuilder
 * @mixin \ooyyee\ui\TabBuilder
 * @method \ooyyee\ui\TabBuilder filter($filter) static 设置列
 * @method \ooyyee\ui\TabBuilder module($module) static 路径
 * @method \ooyyee\ui\TabBuilder tabs($tabs) static 路径
 * @method \think\response\View fetch() static 模板
 */
class TabBuilder extends Facade
{
    public static function getFacadeClass()
    {
        return \ooyyee\ui\TabBuilder::class;
    }
}