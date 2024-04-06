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

namespace App\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Database\Query\Builder;
use Psr\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

#[Command]
class MigrateUI extends HyperfCommand
{
    protected string $description = '迁移 owl-UI 组件到 Hyperf Plus Admin';

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('ui:migrate');
    }

    public function updateNamespace($filePath, $oldNamespace, $newNamespace, $newDirectory)
    {
        $content = file_get_contents($filePath);
        $newContent = str_replace($oldNamespace, $newNamespace, $content);
        $newContent = str_replace([
            'use Illuminate\\Database\\Eloquent\\Model;',
            'use Illuminate\\Database\\Eloquent\\Relations',
            'Cache::rememberForever',
            'use Illuminate\\Database\\Eloquent\\Concerns',
            'extends AdminService',
            'Slowlyo\\OwlAdmin\\Models\\',
            'use Illuminate\\Support\\Facades\\DB;',
            'use Illuminate\\Support\\Facades\\',
            'use Illuminate\\Support\\Traits\\Macroable;',
            'use Illuminate\\Support\\Facades\\Schema;',
            'use Illuminate\\Support\\Facades\\',
            'use Illuminate\\Contracts\\Support\\Arr;',
            'use Illuminate\\Support\\Arr;',
            'use Illuminate\\Contracts\\Support\\Arrayable;',
            'use Illuminate\\Database\\Eloquent\\Builder;',
            'use Slowlyo\\OwlAdmin\\Traits\\UploadTrait;',
            'use UploadTrait;',
            '(filled(',
            'public function jsonSerialize()',
        ], [
            'use Hyperf\\Database\\Model\\Model;',
            'use Hyperf\\Database\\Model\\Relations',
            'cache_has_set',
            'use Hyperf\\Database\\Model\\Concerns',
            'extends AdminBaseService',
            'HPlus\\Admin\\Service\\',
            'use Hyperf\\DbConnection\\Db;',
            'use Hyperf\\Database\\Schema\\',
            'use Hyperf\\Macroable\\Macroable;',
            'use Hyperf\\Database\\Schema\\Schema;',
            'use Hyperf\\Database\\Schema\\',
            'use Hyperf\\Collection\\Arr;',
            'use Hyperf\\Collection\\Arr;',
            'use Hyperf\\Contract\\Arrayable;',
            'use Hyperf\\Database\\Query\\Builder;',
            'use HPlus\\Admin\\Traits\\HasUploadTrait;',
            'use HasUploadTrait;',
            '(check_filled(',
            'public function jsonSerialize(): mixed',
        ], $newContent);

        // Builder::class

        if ($newContent !== $content) {
            // 计算新文件路径
            $newFilePath = str_replace(dirname($filePath), $newDirectory, $filePath);
            $newFileDir = dirname($newFilePath);

            // 确保新目录存在
            if (!is_dir($newFileDir)) {
                mkdir($newFileDir, 0777, true);
            }

            file_put_contents($newFilePath, $newContent);
            $this->info('Updated namespace in: ' . $filePath . ' to ' . $newFilePath);
        }
    }

    public function configure(): void
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    public function handle()
    {
        $this->info('success');

//        $this->updateDirectoryNamespaces(
//            $this->basePath('owl-admin/src/Renderers'),
//            "Slowlyo\\OwlAdmin\\Renderers",
//            "HPlus\\Admin\\Renderers",
//            'packages/admin/src/Renderers',
//        );
//
//        $this->updateDirectoryNamespaces(
//            $this->basePath('owl-admin/src/Services'),
//            "Slowlyo\\OwlAdmin\\Services",
//            "HPlus\\Admin\\Service",
//            'packages/admin/src/Service',
//        );
//
//        $this->updateDirectoryNamespaces(
//            $this->basePath('owl-admin/src/Models'),
//            'Slowlyo\\OwlAdmin\\Models',
//            'HPlus\\Admin\\Model',
//            'packages/admin/src/Model',
//        );
    }

    public function basePath($path)
    {
        return BASE_PATH . '/' . $path;
    }

    public function updateDirectoryNamespaces($directory, $oldNamespace, $newNamespace, $newDirectory): void
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }

            $filePath = $file->getRealPath();
            if (pathinfo($filePath, PATHINFO_EXTENSION) == 'php') {
                $this->updateNamespace($filePath, $oldNamespace, $newNamespace, $newDirectory);
            }
        }
    }
}
