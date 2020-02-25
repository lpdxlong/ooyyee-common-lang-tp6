<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-21
 * Time: 12:29
 */

namespace ooyyee\facade;


use think\Facade;


/**
 * @see \ooyyee\CurrentUser
 * @mixin \ooyyee\CurrentUser
 * @method int uid() static 获取UID
 * @method string name() static 获取user name
 * @method array user() static 获取用户
 * @method array create($uid) static 创建用户
 * @method array isLogin() static 是否已登录
 * @method array isSuperAdmin() static 是否超级管理员
 * @method array permissions() static 获取权限点
 * @method array hasRole($roleId) static 是否拥有什么角色
 * @method array hasPermission($permissionId) static 是否拥有什么权限
 */
class CurrentUser extends Facade
{
    public static function getFacadeClass()
    {
        return \ooyyee\CurrentUser::class;
    }
}