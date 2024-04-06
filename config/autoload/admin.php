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
use Slowlyo\OwlAdmin\Models\AdminUser;

use function Hyperf\Support\env;

return [
    // 应用路由
    'route' => [
        'prefix' => '/api/admin',
        'domain' => null,
        //        'namespace' => 'App\\Admin\\Controllers',
        //        'middleware' => ['admin'],
        // 不包含额外路由, 配置后, 不会追加新增/详情/编辑页面路由
        'without_extra_routes' => [
            '/dashboard',
        ],
    ],

    'database' => [
        'connection' => 'default',
    ],
    'auth' => [
        // 是否开启验证码
        'login_captcha' => env('ADMIN_LOGIN_CAPTCHA', true),
        // 是否开启认证
        'enable' => true,
        // 是否开启鉴权
        'permission' => true,
        // token 有效期 (分钟), 为空则不会过期
        'token_expiration' => null,
        'guard' => 'admin',
        'guards' => [
            'admin' => [
                'driver' => 'sanctum',
                'provider' => 'admin',
            ],
        ],
        'providers' => [
            'admin' => [
                'driver' => 'eloquent',
                'model' => AdminUser::class,
            ],
        ],
        'except' => [
        ],
    ],
    'show_development_tools' => env('ADMIN_SHOW_DEVELOPMENT_TOOLS', true),
    'layout' => [
        // 浏览器标题, 功能名称使用 %title% 代替
        'title' => '%title% | HPlus Admin',
        'header' => [
            // 是否显示 [刷新] 按钮
            'refresh' => true,
            // 是否显示 [暗色模式] 按钮
            'dark' => true,
            // 是否显示 [全屏] 按钮
            'full_screen' => true,
            // 是否显示 [主题配置] 按钮
            'theme_config' => true,
        ],
        /*
         * keep_alive 页面缓存黑名单
         *
         * eg:
         * 列表: /user
         * 详情: /user/:id
         * 编辑: /user/:id/edit
         * 新增: /user/create
         */
        'keep_alive_exclude' => [],
        // 底部信息
        'footer' => '<a href="https://github.com/slowlyo/owl-admin" target="_blank">HPlus Admin</a>',
    ],
];
