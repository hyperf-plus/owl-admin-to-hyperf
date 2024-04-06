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

use HPlus\UI\Beans\UISettingBean;
use HPlus\UI\UI;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
#[Command]
class UITest extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('ui:test');
    }

    public function configure(): void
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    public function handle()
    {
        p(UI::view(new UISettingBean('title', 'logo', 'api_prefix')));
    }
}
