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

class ApiJsSdkException extends Exception
{
    private string $jump_url;

    public function __construct(int $code = 400, $message = '', $jump_url = '')
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $this->message = $message;
        $this->code = $code;
        $this->jump_url = $jump_url;
    }

    public function getJumpUrl(): string
    {
        return $this->jump_url;
    }

    public function setJumpUrl(string $jump_url): void
    {
        $this->jump_url = $jump_url;
    }
}
