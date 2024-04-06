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

use Hyperf\Context\Context;
use Hyperf\HttpServer\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class CorsMiddleware.
 */
class CorsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Response $response */
        $response = Context::get(ResponseInterface::class);
        $origin = trim($request->getHeaderLine('origin'), '/');
        $response = $response->withHeader('Access-Control-Allow-Origin', empty($origin) ? '*' : $origin)
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('access-control-expose-headers', 'x-request-id')
            ->withHeader('Access-Control-Allow-Methods', 'GET,POST,OPTIONS,PUT,DELETE')
            ->withHeader('Access-Control-Allow-Headers', 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization,X-Requested-With,X-Request-Id,cookie,x-sdk-version,x-verify-code,x-api-secret,Version,x-xsrf-token');
        Context::set(ResponseInterface::class, $response);
        if ($request->getMethod() == 'OPTIONS') {
            return $response;
        }
        return $handler->handle($request);
    }
}
