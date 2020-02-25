<?php

namespace ooyyee\exception;


class NoAuthException extends \Exception {
	public function __construct(){
		parent::__construct('没有权限', 401);
	}
}

?>