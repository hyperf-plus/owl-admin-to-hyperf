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

namespace App\Kernel\Model;

use Hyperf\Database\Commands\Ast\ModelUpdateVisitor as Visitor;

class ModelUpdateVisitor extends Visitor
{
    protected function formatDatabaseType(string $type): ?string
    {
        switch ($type) {
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
                return 'integer';
            case 'timestamp':
            case 'datetime':
                return 'datetime';
            case 'json':
                return 'json';
            case 'bool':
            case 'boolean':
                return 'boolean';
            default:
                return null;
        }
    }
}
