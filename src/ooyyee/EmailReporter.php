<?php

namespace ooyyee;
use ooyyee\mail\PHPMailer;
use think\facade\View;
use think\facade\Request;
class EmailReporter
{
	/**
	 * 
	 * @param \Exception|array $mailBody
	 * @param array $params
     * @return array
     * @throws
	 */
	public static function post($mailBody,$params=[]){
		//Create a new PHPMailer instance


        $config=config('mail');


		$mail = new PHPMailer();
		
		//是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
		$mail->SMTPDebug = 0;
		
		//使用smtp鉴权方式发送邮件
		$mail->isSMTP();
		
		//smtp需要鉴权 这个必须是true
		$mail->SMTPAuth=true;
		
		//链接qq域名邮箱的服务器地址
		$mail->Host = 'smtp.163.com';//163邮箱：smtp.163.com
		
		//设置使用ssl加密方式登录鉴权
		$mail->SMTPSecure = 'ssl';//163邮箱就注释
		
		//设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
		$mail->Port = 465;//163邮箱：25
		
		//设置smtp的helo消息头 这个可有可无 内容任意
		// $mail->Helo = 'Hello smtp.qq.com Server';
		//设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
		$mail->Hostname = $config['hostname'];
		
		//设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
		$mail->CharSet = 'UTF-8';
		
		//设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
		$mail->FromName = $config['name'];
		
		//smtp登录的账号 这里填入字符串格式的qq号即可
		$mail->Username =$config['username'];
		
		//smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
		$mail->Password =$config['password'];//163邮箱也有授权码 进入163邮箱帐号获取
		
		//设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
		$mail->From =$config['from'];
		
		//邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
		$mail->isHTML();
		
		//设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
		$mail->addAddress($config['address']);

		$mail->Subject = $config['subject'];
		
		//添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
		
		if($mailBody instanceof \Exception){
            $mailBody=array(
					'code'=>$mailBody->getCode(),
					'file'=>$mailBody->getFile(),
					'line'=>$mailBody->getLine(),
					'message'=>$mailBody->getMessage(),
					'trace'=>$mailBody->getTraceAsString(),
					'time'=>date('Y-m-d H:i:s'),
                    'ip'=>request()->ip(),
			);
		}

        $url=Request::instance()->url(true);
        $params['session']=$_SESSION??array();
        if($url){
            $params['url']=$url;
        }

		$html=View::instance()->fetch(__DIR__.'/mail.html',array('data'=>$mailBody,'params'=>$params));
		
		$mail->Body = $html;
		$result = $mail->send();
		return ['errcode'=>$result?0:1,'errmsg'=>$mail->ErrorInfo];
	}
}

