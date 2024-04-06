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

class ChatException extends Exception
{
    public function __construct($message, int $code = 400)
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $this->message = $message;
        $this->code = $code;
    }
}
