<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-03-10
 * Time: 10:25
 */
namespace ooyyee\db\search\facade;
use think\db\Where;
use think\Facade;

/**
 * Class WhereBuilder
 * @method Where build($options) static 生成Where
 * @package ooyyee\db\search\facade
 */
class WhereBuilder extends Facade
{
    protected static function getFacadeClass()
    {
        return \ooyyee\db\search\WhereBuilder::class;
    }
}