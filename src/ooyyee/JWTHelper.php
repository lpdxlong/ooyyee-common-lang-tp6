<?php

namespace ooyyee;


use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\ValidationData;

class JWTHelper
{

    /**
     * 配置秘钥加密
     * @return Configuration
     */
    public static function getConfig(): Configuration
    {
        return Configuration::forSymmetricSigner(
        // You may use any HMAC variations (256, 384, and 512)
            new Sha256(),
            // replace the value below with a key of your own!
            InMemory::base64Encoded(config('jwt.key'))
        // You may also override the JOSE encoder/decoder if needed by providing extra arguments here
        );
    }


    /**
     * @param $uid
     * @param int $expires
     * @return string
     */
    public static function create($uid, int $expires=7200):string {

        $config = self::getConfig();
        $builder=   $config->builder();
        $url=config('jwt.url');
        // 设置发行人
        $builder->issuedBy($url);
        // 设置接收人
        $builder->permittedFor($url);
        // 设置id
        $builder->identifiedBy(config('jwt.id'));
        // 设置生成token的时间
        $dateTime = new \DateTimeImmutable();
        $builder->issuedAt($dateTime);
    
        // 设置过期时间
        $builder->expiresAt($dateTime->add(\DateInterval::createFromDateString($expires.' seconds')));
        // 给token设置一个id
        $builder->withClaim('uid', $uid);

        // 获取生成的token
        $token = $builder->getToken($config->signer(), $config->signingKey());
        return $token->toString();
    }

    /**
     * @param $token
     * @return array
     */
    public static function validate($token):array {


        $config = self::getConfig();
        $token =$config->parser()->parse($token);

        $url=config('jwt.url');
        //Lcobucci\JWT\Validation\Constraint\IdentifiedBy: 验证jwt id是否匹配
//Lcobucci\JWT\Validation\Constraint\IssuedBy: 验证签发人参数是否匹配
//Lcobucci\JWT\Validation\Constraint\PermittedFor: 验证受众人参数是否匹配
//Lcobucci\JWT\Validation\Constraint\RelatedTo: 验证自定义cliam参数是否匹配
//Lcobucci\JWT\Validation\Constraint\SignedWith: 验证令牌是否已使用预期的签名者和密钥签名
//Lcobucci\JWT\Validation\Constraint\ValidAt: 验证要求iat，nbf和exp（支持余地配置）

        //验证jwt id是否匹配
        $validate_jwt_id = new IdentifiedBy(config('jwt.id'));
        $config->setValidationConstraints($validate_jwt_id);
        //验证签发人url是否正确
        $validate_issued = new IssuedBy($url);
        $config->setValidationConstraints($validate_issued);
        //验证客户端url是否匹配
        $validate_aud = new PermittedFor($url);
        $config->setValidationConstraints($validate_aud);

        //验证是否过期
        $timezone = new \DateTimeZone('Asia/Shanghai');
        $now = new SystemClock($timezone);
        $validate_jwt_at = new ValidAt($now);
        $config->setValidationConstraints($validate_jwt_at);

        $constraints = $config->validationConstraints();

        try {
            $config->validator()->assert($token, ...$constraints);
        } catch (RequiredConstraintsViolated $e) {
            return array('errcode'=>400001,'errmsg'=>$e->getMessage(),''=>$e->violations());
        }
        return array('errcode'=>0,'uid'=>$token->claims()->get('uid'));
    }
}

