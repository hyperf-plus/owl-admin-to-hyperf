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

namespace App\Controller;

use HPlus\UI\Beans\UISettingBean;
use HPlus\UI\UI;

use function Hyperf\Config\config;

class IndexController extends AbstractController
{
    public function index()
    {
        $setting = new UISettingBean();
        $setting->title = 'Hyperf Admin';
        $setting->apiPrefix = config('admin.route.prefix');
        return $this->response->html(UI::view($setting));
    }
}
