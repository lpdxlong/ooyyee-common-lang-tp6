<?php

namespace ooyyee\widget;

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\View;


/**
 * ThinkPHP Widget类 抽象类
 *
 * @category Think
 * @package Think
 * @subpackage Core
 * @author liu21st <liu21st@gmail.com>
 */
abstract class Widget
{

    // 使用的模板引擎 每个Widget可以单独配置不受系统影响
    protected $template = '';

    /**
     * 渲染输出 render方法是Widget唯一的接口
     * 使用字符串返回 不能有任何输出
     *
     * @access public
     * @param mixed $data
     *            要渲染的数据
     * @return string
     */
    abstract public function render($data);

    /**
     * 渲染模板输出 供render方法内部调用
     * @param string $templateFile
     * @param array $var
     * @return string
     * @throws \Exception
     */
    protected function renderFile($templateFile = '', $var = [])
    {


        if (!is_file($templateFile)) {
            // 自动定位模板文件
            $name = str_replace('widget\\', '', get_class($this));
            $filename = empty($templateFile) ? $name : $templateFile;
            $templateFile = __DIR__ . '/view/' . $filename  .'.html';
            if (!is_file($templateFile)) {
                dump('模板不存在' . '[' . $templateFile . ']');
            }
        }



        return View::fetch($templateFile, $var);

    }
}
