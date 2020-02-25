<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-20
 * Time: 17:55
 */

namespace ooyyee\facade;


use think\Facade;

/**
 * @see \ooyyee\Upload
 * @mixin \ooyyee\Upload
 * @method string getFullName($format,$ext) static 获取图片名称
 * @method array upload($format, $file,$defaultExtension='jpg') static 上传
 *@method array catchImage($imgUrl,$fileName) static 获取远程图片
 */
class Upload extends Facade
{
    public static function getFacadeClass()
    {
        return \ooyyee\Upload::class;
    }
}