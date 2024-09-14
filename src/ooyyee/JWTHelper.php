<?php

namespace ooyyee;


use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\ValidationData;

class JWTHelper
{
    /**
     * @param $uid
     * @param int $expires
     * @return string
     */
    public static function create($uid,$expires=7200):string {
        $builder = new Builder();
        $signer = new Sha256();
        $key=new Key(config('jwt.key'));
        $url=config('jwt.url');
        // 设置发行人
        $builder->issuedBy($url);
        // 设置接收人
        $builder->permittedFor($url);
        // 设置id
        $builder->identifiedBy(config('jwt.id'), false);
        // 设置生成token的时间
        $dateTime = new \DateTimeImmutable();
        $builder->issuedAt($dateTime);
    
        // 设置过期时间
        $builder->expiresAt($dateTime->add(\DateInterval::createFromDateString($expires.' seconds')));
        // 给token设置一个id
        $builder->withClaim('uid', $uid);

        // 获取生成的token
        $token = $builder->getToken($signer, $key);
        return $token->toString();
    }

    /**
     * @param $token
     * @return array
     */
    public static function validate($token):array {
        $parser=new Parser();
        $token =$parser->parse((String) $token);
        $signer = new Sha256();
        if (!$token->verify($signer, config('jwt.key'))) {
            return array('errcode'=>400002,'errmsg'=>'token签名不正确!'); //签名不正确
        }
        if ($token->isExpired()) {
            return array('errcode'=>400003,'errmsg'=>'token已过期');
        }
        $url=config('jwt.url');
        $validationData = new ValidationData();
        $validationData->setIssuer($url);
        $validationData->setAudience($url);
        $validationData->setId(config('jwt.id'));//自字义标识
        $result=$token->validate($validationData);
        if(!$result){
            return array('errcode'=>400004,'errmsg'=>'token验证失败!');
        }
        return array('errcode'=>0,'uid'=>$token->getClaim('uid'));
    }
}

