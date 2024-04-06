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

namespace App\Exception\Handler;

use App\Exception\ApiAmisException;
use App\Exception\ApiJsSdkException;
use App\Exception\ApiJsSdkUserException;
use App\Exception\BusinessException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Exception\NotFoundException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Qbhy\HyperfAuth\Exception\UnauthorizedException;
use Qbhy\SimpleJwt\Exceptions\InvalidTokenException;
use Qbhy\SimpleJwt\Exceptions\SignatureException;
use Qbhy\SimpleJwt\Exceptions\TokenBlacklistException;
use Qbhy\SimpleJwt\Exceptions\TokenExpiredException;
use Qbhy\SimpleJwt\Exceptions\TokenRefreshExpiredException;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        if ($throwable instanceof NotFoundException) {
            throw new $throwable();
        }

        if ($throwable instanceof ApiAmisException) {
            return $response->withHeader('Server', 'UUAI-API')->withStatus(200)->withBody(new SwooleStream(json_encode([
                'msg' => $throwable->getMessage(),
                'status' => 1,
            ], JSON_UNESCAPED_UNICODE)));
        }

        $data = [
            'code' => $throwable->getCode(),
            'status' => 1,
            'message' => $throwable->getMessage(),
            //            'info' => $throwable->getMessage(),
        ];
        $status = 200;
        switch (true) {
            case $throwable instanceof ApiJsSdkException:
                $status = $throwable->getCode();
                $data['jump_ur'] = $throwable->getJumpUrl();
                break;
            case $throwable instanceof BusinessException:
                $status = 400;
                break;
            case $throwable instanceof ApiJsSdkUserException:
                $status = 401;
                $data['login_url'] = $throwable->getLoginUrl();
                $data['client_id'] = $throwable->getClientId();
                break;
            case $throwable instanceof UnauthorizedException:
                $status = 200;
                $data['code'] = 401;
                break;
            case $throwable instanceof SignatureException:
            case $throwable instanceof InvalidTokenException:
            case $throwable instanceof TokenRefreshExpiredException:
            case $throwable instanceof TokenBlacklistException:
            case $throwable instanceof TokenExpiredException:
                $status = 401;
                break;
        }

        return $response->withHeader('Server', 'UUAI-API')->withStatus($status)->withBody(new SwooleStream(json_encode($data, JSON_UNESCAPED_UNICODE)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
