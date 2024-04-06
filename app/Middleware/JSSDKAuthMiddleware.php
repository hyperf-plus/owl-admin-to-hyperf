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

namespace App\Middleware;

use App\Constants\ErrorCode;
use App\Exception\ApiJsSdkException;
use App\Exception\BusinessException;
use Hyperf\Di\Exception\NotFoundException;
use Hyperf\HttpServer\Router\Dispatched;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JSSDKAuthMiddleware implements MiddlewareInterface
{
    public $ignore = [
        '/v1/jssdk/ai/config/{app_name}',
        '/v1/jssdk/corp/info',
        '/v1/jssdk/ai/share',
        '/v1/jssdk/user/team/share/info',
        '/v1/jssdk/user/login',
    ];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Dispatched $router */
        $router = $request->getAttribute(Dispatched::class);
        if (! $router->isFound()) {
            throw new NotFoundException('接口不存在');
        }

        if (in_array($router->handler->route, $this->ignore)) {
            return $handler->handle($request);
        }

        if (! auth('open_jssdk')->check()) {
            throw new ApiJsSdkException(ErrorCode::STATUS_UNAUTHORIZED, '无权限');
            // throw new BusinessException(403, '无权限');
        }
        return $handler->handle($request);
    }
}
