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
 * @see \ooyyee\ui\TableBuilder
 * @mixin \ooyyee\ui\TableBuilder
 * @method \ooyyee\ui\TableBuilder column($columns) static 设置列
 * @method \ooyyee\ui\TableBuilder setting($setting,$type=0) static 配置
 * @method \think\response\View fetch($template) static 模板
 * @method \ooyyee\ui\TableBuilder toolbar($setting,$type=0) static 设置toolbar
 * @method \think\response\Json result($data,$total) static 返回结果
 */
class TableBuilder extends Facade
{
    public static function getFacadeClass()
    {
        return \ooyyee\ui\TableBuilder::class;
    }
}