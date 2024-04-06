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

namespace App\Exception;

use Exception;

class ApiJsSdkUserException extends Exception
{
    private string $login_url;

    private string $client_id;

    public function __construct(string $message, int $code = 401)
    {
        $this->message = $message;
        $this->code = $code;
    }

    public function getClientId(): string
    {
        return $this->client_id;
    }

    public function setClientId(string $client_id): void
    {
        $this->client_id = $client_id;
    }

    public function getLoginUrl(): string
    {
        return $this->login_url;
    }

    public function setLoginUrl(string $login_url): void
    {
        $this->login_url = $login_url;
    }
}
