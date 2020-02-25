<?php
namespace ooyyee\exception;

class NotFoundException extends \Exception
{
    public function __construct(){
        parent::__construct('访问的页面不存在', 404);
    }
}

?>