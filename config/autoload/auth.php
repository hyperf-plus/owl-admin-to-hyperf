<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use HPlus\Admin\Model\AdminUser;
use Qbhy\HyperfAuth\Guard\JwtGuard;
use Qbhy\HyperfAuth\HyperfRedisCache;
use Qbhy\HyperfAuth\Provider\EloquentProvider;
use Qbhy\SimpleJwt\Encoders;
use Qbhy\SimpleJwt\EncryptAdapters as Encrypter;

use function Hyperf\Support\env;
use function Hyperf\Support\make;

return [
    'default' => [
        'guard' => 'admin',
        'provider' => 'users',
    ],
    'guards' => [
        'admin' => [
            'driver' => JwtGuard::class,
            'provider' => 'admin',
            /*
             * 以下是 simple-jwt 配置
             * 必填
             * jwt 服务端身份标识
             */
            'secret' => env('SIMPLE_JWT_SECRET'),

            /*
             * 可选配置
             * jwt 默认头部token使用的字段
             */
            'header_name' => env('JWT_HEADER_NAME', 'Authorization'),

            /*
             * 可选配置
             * jwt 生命周期，单位秒，默认一天
             */
            'ttl' => (int) env('SIMPLE_JWT_TTL', 60 * 60 * 24),

            /*
             * 可选配置
             * 允许过期多久以内的 token 进行刷新，单位秒，默认一周
             */
            'refresh_ttl' => (int) env('SIMPLE_JWT_REFRESH_TTL', 60 * 60 * 24 * 7),

            /*
             * 可选配置
             * 默认使用的加密类
             */
            'default' => Encrypter\SHA1Encrypter::class,

            /*
             * 可选配置
             * 加密类必须实现 Qbhy\SimpleJwt\Interfaces\Encrypter 接口
             */
            'drivers' => [
                Encrypter\PasswordHashEncrypter::alg() => Encrypter\PasswordHashEncrypter::class,
                Encrypter\CryptEncrypter::alg() => Encrypter\CryptEncrypter::class,
                Encrypter\SHA1Encrypter::alg() => Encrypter\SHA1Encrypter::class,
                Encrypter\Md5Encrypter::alg() => Encrypter\Md5Encrypter::class,
            ],

            /*
             * 可选配置
             * 编码类
             */
            'encoder' => new Encoders\Base64UrlSafeEncoder(),
            //            'encoder' => new Encoders\Base64Encoder(),

            /*
             * 可选配置
             * 缓存类
             */
            // 如果需要分布式部署，请选择 redis 或者其他支持分布式的缓存驱动
            'cache' => function () {
                return make(HyperfRedisCache::class);
            },

            /*
             * 可选配置
             * 缓存前缀
             */
            'prefix' => env('SIMPLE_JWT_PREFIX', 'default'),
        ],
    ],
    'providers' => [
        'admin' => [
            'driver' => EloquentProvider::class,
            'model' => AdminUser::class,
        ],
    ],
];
