<?php
namespace ooyyee;
class Http {

    /**
     * @param int $type
     * @return mixed
     */
	public static function getClientIp($type = 0) {
		$type = $type ? 1 : 0;
		static $ip = NULL;
		if ($ip !== NULL) {
            return $ip[$type];
        }
		if (isset ( $_SERVER['HTTP_X_FORWARDED_FOR'] )) {
			$arr = explode ( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
			$pos = array_search ( 'unknown', $arr );
			if (false !== $pos) {
                unset ( $arr[$pos] );
            }
			$ip = trim ( $arr[0] );
		} elseif (isset ( $_SERVER['HTTP_CLIENT_IP'] )) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (isset ( $_SERVER['REMOTE_ADDR'] )) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		// IP地址合法验证
		$long = ip2long ( $ip );
		$ip = $long ? array ($ip,$long ) : array ('0.0.0.0',0 );
		return $ip[$type];
	}

	
	/**
	 * curl 请求
	 *
	 * @param string $url        	
	 * @param string|array $post post 的数据
     * @param  array $headers 头数据
	 * @param int $timeout 超时
     * @return string
	 */
	private static function request($url, $post = '', $headers = array(), $timeout = 60):string {
		$ch = curl_init ();
		$curl_url = $url;
		curl_setopt ( $ch, CURLOPT_URL, $curl_url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, false );
		if ($post) {
			curl_setopt ( $ch, CURLOPT_POST, 1 );
			if (is_array ( $post )) {
				$post = http_build_query ( $post );
			}
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post );
		}
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, $timeout );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt ( $ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1 );
		curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) MicroMessenger/20100101 Firefox/9.0.1' );
		if (! empty ( $headers ) && is_array ( $headers )) {
			curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		}
        return  curl_exec ( $ch );

	}
	/**
	 * HTTP GET 请求
	 *
	 * @param string $url URL
	 *
     * @param bool $json_decode
	 * @param array $headers
	 *        	是否获取header
     * @return array|string
	 */
	public static function get($url,$json_decode=true, $headers = array()) {
		$data= self::request ( $url, null, $headers );
		if($json_decode){
			$data=@json_decode($data,true);
		}
		return $data;
	}

    /**
     * @param $url
     * @param $data
     * @param bool $json_decode
     * @param array $headers
     * @return mixed|string
     */
	public static function post($url, $data, $json_decode=true,$headers = array()) {
		$_headers = array ('Content-Type' => 'application/x-www-form-urlencoded' );
		$headers = array_merge ( $_headers, $headers );
		$data= self::request ( $url, $data, $headers );
		if($json_decode){
			$data=@json_decode($data,true);
		}
		return $data;
	}
	public static function upload($url, $data) {
		$ch = curl_init ( $url );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 50 );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 50 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt ( $ch, CURLOPT_MAXREDIRS, 3 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		$result = curl_exec ( $ch );
		curl_close ( $ch );
		return $result;
	}
	
	// 线程并发抓取函数mfetch：

    /**
     * 多线程
     * @param array $urls
     * @param $method
     * @param int $usleep
     * @return array
     */
	private static function mutilRequest($urls, $method, $usleep = 100000):array {
		$mh = curl_multi_init (); // 始化一个curl_multi句柄
		$handles = array ();
		foreach ( $urls as $key => $param ) {
			$ch = curl_init (); // 始化一个curl句柄
			$url = $param['url']??'';
			$data = $param['params']??array ();
			if (strtolower ( $method ) === 'get') {
				// 据method参数判断是post还是get方式提交数据
				$url = "$url?" . http_build_query ( $data ); // et方式
			} else {
				curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data ); // ost方式
			}
			curl_setopt ( $ch, CURLOPT_URL, $url );
			curl_setopt ( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt ( $ch, CURLOPT_HEADER, 0 );
			curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 60 );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt ( $ch, CURLOPT_TIMEOUT, 60 );
			curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
			curl_setopt ( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
			curl_multi_add_handle ( $mh, $ch );
			$handles[( int ) $ch] = $key;
			// andles数组用来记录curl句柄对应的key,供后面使用，以保证返回的数据不乱序。
		}
		$running = null;
		$status = null;
		$result = array (); // url数组用来记录各个curl句柄的返回值
		do { // 起curl请求，并循环等等1/100秒，直到引用参数"$running"为0
			usleep ( $usleep );
			curl_multi_exec ( $mh, $running );
			while ( ($ret = curl_multi_info_read ( $mh )) !== false ) {
				// 环读取curl返回，并根据其句柄对应的key一起记录到$curls数组中,保证返回的数据不乱序
				$result[$handles[( int ) $ret['handle']]] = $ret;
			}
		} while ( $running > 0 );
		foreach ( $result as $key => $val ) {
			$val['result'] = curl_multi_getcontent ( $val['handle'] );
			curl_close ( $val['handle'] );
			curl_multi_remove_handle ( $mh, $val['handle'] ); // 除curl句柄
            $result[$key]=$val;
		}
		curl_multi_close ( $mh ); // 闭curl_multi句柄
		ksort ( $result );
		return $result;
	}
	
	/**
	 * 多线程执行CURL GET
	 *
	 * @param array $urls
	 *        	要执行的URL 数组
	 *        	$urls=[['url'=>'www.ooyyee.com','params'=>[]]]
	 * @param int $usleep
	 *        	每次间隔时间 微秒
	 * @return array
	 */
	public static function mutilGet(array $urls, $usleep = 10000):array {
		return self::mutilRequest ( $urls, 'get', $usleep );
	}
	/**
	 * 多线程同时执行上传
	 *
	 * @param array $urls
	 *        	要执行的URL 数组
	 *        	$urls=[['url'=>'www.ooyyee.com','params'=>[]]]
	 * @param int $usleep
	 *        	每次间隔时间 微秒
	 * @return array
	 */
	public static function mutilUpload(array $urls, $usleep = 10000):array{
		$mh = curl_multi_init (); // 始化一个curl_multi句柄
		$handles = array ();
		foreach ( $urls as $key => $param ) {
			$ch = curl_init (); // 始化一个curl句柄
            $url = $param['url']??'';
            $data = $param['params']??array ();
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data ); // ost方式
			curl_setopt ( $ch, CURLOPT_URL, $url );
			curl_setopt ( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt ( $ch, CURLOPT_HEADER, 0 );
			curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 30 );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt ( $ch, CURLOPT_TIMEOUT, 30 );
			curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
			curl_setopt ( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
			curl_multi_add_handle ( $mh, $ch );
			$handles[( int ) $ch] = $key;
		}
		$running = null;
		$status = null;
		$result = array (); // url数组用来记录各个curl句柄的返回值
		do { // 起curl请求，并循环等等1/100秒，直到引用参数"$running"为0
			usleep ( $usleep );
			curl_multi_exec ( $mh, $running );
			while ( ($ret = curl_multi_info_read ( $mh )) !== false ) {
				$result[$handles[( int ) $ret['handle']]] = $ret;
			}
		} while ( $running > 0 );
		foreach ( $result as $key => $val ) {
			$val['result'] = curl_multi_getcontent ( $val['handle'] );
			curl_close ( $val['handle'] );
			curl_multi_remove_handle ( $mh, $val['handle'] ); // 除curl句柄
            $result[$key]=$val;
		}
		curl_multi_close ( $mh ); // 闭curl_multi句柄
		ksort ( $result );
		return $result;
	}
	
	/**
	 * 多线程执行CURL POST
	 *
	 * @param array $urls
	 *        	要执行的URL 数组
	 *        	$urls=[['url'=>'www.ooyyee.com','params'=>[]]]
	 * @param int $usleep
	 *        	每次间隔时间 微秒
	 * @return array
	 */
	public static function mutilPost(array $urls, $usleep = 10000):array {
		return self::mutilRequest ( $urls, 'post', $usleep );
	}
	
	/**
	 * 检查是否是微信浏览器打开
	 */
	public static function checkWxAgent():bool {
		$agent = $_SERVER['HTTP_USER_AGENT'];
		return strpos ( $agent, 'MicroMessenger' ) > 0 || strpos ( $agent, 'wxwork' ) > 0;
	}
	/**
	 * 判断企业微信
	 */
	public static function checkWxWorkAgent():bool {

		$agent = $_SERVER['HTTP_USER_AGENT'];
		return strpos ( $agent, 'wxwork' ) > 0;
	}

}

