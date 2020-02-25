<?php

namespace ooyyee;

use think\facade\App;

class Image {
	private static $error;
	/**
	 * 合成图片
	 *
	 * @param string $destImage
	 *        	合成后保存的图片路径
	 * @param string $sourceImage
	 *        	原始图片
	 * @param object|resource|string $compositeImage
	 *        	要合成到原始图片中的小图片
	 * @param array $compositeImageInfo
	 *        	要合成到原始图片中的小图片信息 array('top'=>100,'left'=>100,'width'=>200,'height'=>200);
     * @return bool
	 */
	public static function compositeImage($destImage, $sourceImage, $compositeImage, $compositeImageInfo):bool {
		try {
			// 图片信息
			$sInfo = self::getImageInfo ( $sourceImage );
			// 建立图像
			$sCreateFun = 'imagecreatefrom' . $sInfo ['type'];
			$sImage = $sCreateFun ( $sourceImage );
			
			// 图像位置,默认为右下角右对齐
			$posX = $compositeImageInfo ['left'];
			$posY = $compositeImageInfo ['top'];

            $compositeInfo = self::getImageInfo ( $compositeImage );
			if(is_string($compositeImage)){

				// 建立图像
				$compositeInfoCreateFun = 'imagecreatefrom' . $compositeInfo ['type'];
				$compositeImage = $compositeInfoCreateFun ( $compositeImage );

			}




			// 生成混合图像
			imagecopymerge ( $sImage, $compositeImage, $posX, $posY, 0, 0, $compositeImageInfo ['width'], $compositeImageInfo ['height'], 100 );
			
			// 输出图像
			$ImageFun = 'Image' . $sInfo ['type'];
			// 保存图像
			$ImageFun ( $sImage, $destImage );
			imagedestroy ( $sImage );
            imagedestroy($compositeImage);

			return true;
		} catch ( \Exception $e ) {
			self::$error=$e;
			return false;
		}
	}

    /**
     * @return mixed
     */
	public static function getLastError(){
		return self::$error;
	}

    /**
     * @param $img
     * @return array|bool
     */
    public static function getImageInfo($img) {
		$imageInfo = getimagesize ( $img );
		if ($imageInfo !== false) {
			$imageType = strtolower ( substr ( image_type_to_extension ( $imageInfo [2] ), 1 ) );
			$imageSize = filesize ( $img );
			$info = array (
				'width' => $imageInfo [0],
				'height' => $imageInfo [1],
				'type' => $imageType,
				'size' => $imageSize,
				'mime' => $imageInfo ['mime'] );
			return $info;
		}
        return false;
	}

    /**
     * 文件路径转URL
     * @param string $file
     * @param bool $time
     * @return string
     */
    public static function fileToURL($file,$time=true):string {

        $path=str_replace(app()->getRootPath().'public/', '', $file);

        $protocol=request()->isSsl()?'https://':'http://';
        $domain=request()->domain();
        if(!$domain){
            $domain=$protocol.config('url_domain_root');
        }
        $url= $domain.'/'.$path;
        if($time){
            $url.='?t='.time();
        }
        return $url;

    }
}
