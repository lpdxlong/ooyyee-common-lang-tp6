<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-11-29
 * Time: 12:20
 */

namespace ooyyee;
use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Etag;
use Qiniu\Http\Error;
use Qiniu\Region;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use Qiniu\Zone;
use think\facade\Db;
use think\file\UploadedFile;


class Upload
{

    private  $auth;

    public function __construct()
    {

    }

    /**
     * @param $format
     * @param $ext
     * @return string
     */
    public  function getFullName($format,$ext):string
    {
        //替换日期事件
        $t = time();
        $d = explode('-', date('Y-y-m-d-H-i-s'));
        $format = str_replace(['{yyyy}','{yy}','{mm}','{dd}','{hh}','{ii}','{ss}','{time}'], [$d[0],$d[1],$d[2],$d[3], $d[4],$d[5],$d[6],$t], $format);

        //替换随机字符串
        $randNum = random_int(1, mt_getrandmax()) . random_int(1, mt_getrandmax());
        if (preg_match('/\{rand\:([\d]*)\}/i', $format, $matches)) {
            $format = preg_replace('/\{rand\:[\d]*\}/i', substr($randNum, 0, $matches[1]), $format);
        }
         return $format . $ext;
    }


    /**
     * @return Auth
     */
    public  function createAuth():Auth{
        if(!$this->auth){
            $this->auth = new Auth(config('qiniu.accessKey'),config('qiniu.secretKey'));
        }
        return $this->auth;
    }

    /**
     * @param $type
     * @return string
     */
    public function getToken($type):string {
         $key=config('qiniu.accessKey').'_'.$type.'_qiniu_token';
         $token=cache($key);
         if($token){
             return $token;
         }
        $bucket=$this->fetchBucket($type);
        $policy = array();
        $token=$this->createAuth()->uploadToken($bucket, null, 3600, $policy);
        cache($key,$token,3000);
        return $token;
    }

    /**
     * @param $type
     * @return mixed
     */
    public  function fetchBucket($type){
        $type=$type ==='video'?'video':'default';
        $array=config('qiniu.'.$type);
        return $array['bucket'];
    }

    /**
     * @param $type
     * @return mixed
     */
    public  function domain($type){
        $type=$type ==='video'?'video':'default';
        $array=config('qiniu.'.$type);
        return $array['domain'];
    }

    /**
     * @param string $format  生成文件名的格式
     * @param UploadedFile $file
     * @param string $defaultExtension
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public   function upload($format, $file,$defaultExtension='jpg'):array
    {

        $hash=Etag::sum($file->getPathname());
        $db=Db::connect(config('upload.database'))->name(config('upload.table'));
        $myFile=$db->where('hash',$hash[0])->find();
        if($myFile){
            return ['errcode'=>0,'data'=>$myFile,'url'=>$this->domain($myFile['type']).$myFile['key']];
        }
        
        $extension=$file->getOriginalExtension();


        $extension=$extension?:$defaultExtension;
        $type ='image';
        if($extension ==='mp4'){
            $type='video';
        }
        $fileName=$this->getFullName($format,'.'.$extension);
        $token=$this->getToken($type);
        $uploadMgr = new UploadManager(new Config(Region::regionHuabei()));
        [$res,$error]= $uploadMgr->putFile($token, $fileName, $file->getPathname());
        if($error && $error instanceof Error){
            return ['errcode'=>1,'errmsg'=>$error->message()];
        }

        $myFile=[
            'hash'=>$hash[0],
            'title'=>$file->getOriginalName(),
            'type'=>$type,
            'storage'=>'qiniu',
            'key'=>$res['key'],
            'path'=>'qiniu://'.$res['key'],
            'size'=>$file->getSize(),
        ];

        $db->insert($myFile);
        return ['errcode'=>0,'data'=>$myFile,'url'=>$this->domain($type).$res['key']];
    }

    /**
     * @param $imgUrl
     * @param $fileName
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */

    public function catchImage($imgUrl, $fileName){
        $db=Db::connect(config('upload.database'))->name(config('upload.table'));
        $myFile=$db->where('original',$imgUrl)->find();
        if($myFile){
            return ['errcode'=>0,'data'=>$myFile,'url'=>$this->domain('image').$myFile['key']];
        }
        $auth=$this->createAuth();
        $bucket=$this->fetchBucket('image');
        $uploadMgr = new BucketManager($auth,new Config(Zone::zone1()));
        list($res,$error)=   $uploadMgr->fetch($imgUrl, $bucket, $fileName);
        if($error && $error instanceof Error){
            return ['errcode'=>1,'errmsg'=>$error->message()];
        }

        $myFile=[
            'hash'=>$res['hash'],
            'title'=>'fetch image',
            'type'=>'image',
            'storage'=>'qiniu',
            'key'=>$res['key'],
            'path'=>'qiniu://'.$res['key'],
            'size'=>$res['fsize'],
        ];
        $db=Db::connect(config('upload.database'))->name(config('upload.table'));
        $db->insert($myFile);
        return ['errcode'=>0,'data'=>$res,'url'=>$this->domain('image').$res['key']];
    }
}