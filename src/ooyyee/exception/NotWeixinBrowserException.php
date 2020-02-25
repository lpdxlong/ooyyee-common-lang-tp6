<?php
namespace ooyyee\exception;

use think\Exception;

/**
 * 不是微信浏览器
 *
 * @author lpdx111
 *        
 */
class NotWeixinBrowserException extends Exception
{
    /**
     * 错误的请求异常
     *
     * @author lpdx111
     *        
     */
    public function __construct()
    {
        parent::__construct('请使用微信浏览器访问', 400);
    }
}

