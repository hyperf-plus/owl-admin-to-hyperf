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
use App\Model\AdminSetting;
use App\Model\ApplicationConfig;
use App\Model\CorpEmployee;
use App\Model\Corps;
use App\Model\CorpUserRecord;
use App\Model\DevelopAppPermissions;
use App\Model\PaymentPlan;
use App\Model\UserVipPlan;
use App\Model\UserWalletFlow;
use Carbon\Carbon;
use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\RedisDriver;
use Hyperf\Context\ApplicationContext;
use Hyperf\Redis\Redis;
use Psr\EventDispatcher\EventDispatcherInterface;
use UUPT\Contract\Exception\BusinessException;

if (! function_exists('app_event_dispatch')) {
    function app_event_dispatch(object $object)
    {
        ApplicationContext::getContainer()->get(EventDispatcherInterface::class)->dispatch($object);
        return true;
    }
}

if (! function_exists('array_filter_null')) {
    function array_filter_null($arr, $empty_array = false)
    {
        return array_filter($arr, function ($item) use ($empty_array) {
            if ($item === '' || $item === null || (is_array($item) && $empty_array && empty($item))) {
                return false;
            }
            return true;
        });
    }
}

if (! function_exists('cache_clear_prefix')) {
    function cache_clear_prefix($key)
    {
        /** @var CacheManager $manager */
        $manager = ApplicationContext::getContainer()->get(CacheManager::class);
        /** @var RedisDriver $driver */
        $driver = $manager->getDriver();
        $driver->clearPrefix($key);
    }
}

if (! function_exists('get_millisecond')) {
    // 毫秒级时间戳
    function get_millisecond()
    {
        return round(microtime(true) * 1000);
    }
}

if (! function_exists('model_limit')) {
    # 满了后
    function model_limit($userId, $modelId = 0, $limit = 10)
    {
        $container = ApplicationContext::getContainer();
        $redis = $container->get(Redis::class);
        # GPT4下的沟通限制 3小时50次
        $windowSize = 3600; // 时间窗口大小，单位为秒（1小时）
        // 根据模型ID和用户ID生成一个Redis Sorted Set的键名
        $sortedSetKey = "{$modelId}:{$userId}:sessions:new";
        // 获取当前时间戳
        $currentTime = time();
        $time = (string) (time() - $windowSize);
        // 移除过期会话（分数范围：负无穷大 到 当前时间戳 - 时间窗口大小）
        $redis->zRemRangeByScore($sortedSetKey, '-inf', $time);
        // 获取当前活动会话数量
        $activeSessions = $redis->zCount($sortedSetKey, $time, '+inf');
        // 判断当前活动会话数量是否超过最大会话数
        if ($activeSessions >= $limit) {
            throw new BusinessException("访问频次受限：已达最大会话次数（{$limit}）", 400);
        }
        // 添加新会话（以当前时间戳为分数）
        $redis->zAdd($sortedSetKey, $currentTime, $currentTime);
        // 为键设置过期时间，以防止无限制地占用存储空间（过期时间设置为：当前时间 + 时间窗口大小）
        $redis->expire($sortedSetKey, $windowSize);
        return true;
    }
}

/*
 * 返回扣key数量
 *
 * @param $user_id  int 用户ID
 * @param $model_id int 模型ID
 * @param $is_cache bool 是否走缓存
 *
 * @return int|mixed
 */
if (! function_exists('check_user_plan')) {
    function check_user_plan($user_id, $model = '', $money = 0, $is_cache = true)
    {
        //        $model = str_replace('-0613','',$model);
        $models = Cache::remember('modules', 120, function () {
            return ChatModel::query()->where('state', 1)->get(['id', 'slug']);
        });
        $model_id = $models->firstWhere('slug', $model)->id ?? 0;
        $plan = get_app_plan($user_id, $model_id, $is_cache);

        # 不限量 type == 1
        # 检测当前模型是否超限 如果不限的话就不用检测了。
        if ($plan['win_count'] > 0) {
            model_limit($user_id, $model_id, $plan['win_count']);
        }
        # 检测是否需要扣费， 如果不限量就结束，继续前进
        if ($plan['type'] == 1) {
            return 0;
        }
        # 需要扣费，检测是否费用足够
        $user = Users::select(['is_vip', 'count', 'total_count'])->find($user_id);
        # 总量减去已使用，是否大于当前模型扣费
        if ($user->total_count - $user->count < ($plan['dec_key_number'] + $money)) {
            throw new ApiException('您的key不足，请充值！', 5001);
        }
        return $plan['dec_key_number'] ?? 0;
    }
}

if (! function_exists('user_plan_clear_cache')) {
    function user_plan_clear_cache($user_id = null)
    {
        return cache()->delete('new_user_pla1n:' . $user_id);
    }
}

/*
 * 获取用户的付费方案
 *
 * @param $user_id  int 用户ID
 * @param $model_id int 模型ID
 * @param $is_cache bool 是否走缓存
 *
 * @return array|mixed|object
 */
if (! function_exists('get_app_plan')) {
    function get_app_plan($org_name, $app_id = null, $user_id = null, $is_cache = true)
    {
        # todo 用户ID 如果穿了的话，就吧用户的拉出来，融合一起即可！

        return cache_has_set('app_plan:' . $org_name . ':' . $app_id, static function () use ($org_name, $app_id) {
            return DevelopAppPermissions::with('api:id,title,api,qps,key')
                ->where('organization', $org_name)
                ->when($app_id, function ($query) use ($app_id) {
                    $query->where('app_id', $app_id);
                })
                ->get(['id', 'develop_permission_id', 'type'])->map(function ($item) {
                    return [
                        'dec_key_number' => intval($item->api->key),
                        'hour_limit' => intval($item->api->qps),
                        'name' => $item->api->title,
                        'type' => $item->type ?? 0,
                        'engine' => $item->api->api,
                    ];
                })->keyBy('engine')->toArray();
        }, 20);
    }
}

/*
 * 付费计划-开发者版
 */
if (! function_exists('get_dev_plan')) {
    function get_dev_plan($app_id = null, $user_id = null, $corp_id = null, $is_cache = true)
    {
        //        return cache_has_set("dev_app_plan:" . $user_id . ':' . $app_id, static function () use ($org_name, $app_id, $user_id, $corp_id) {
        // 开发者自建应用 直接取api配置
        $app_plans = new stdClass();
        $apis = DevelopAppPermissions::with('api:id,title,api,qps,key')
            ->when($app_id, function ($query) use ($app_id) {
                $query->where('app_id', $app_id);
            })
            ->get(['id', 'develop_permission_id', 'type'])->map(function ($item) {
                if (! empty($item->api)) {
                    return [
                        'dec_key_number' => intval($item->api->key),
                        'time_limit' => intval($item->api->qps),
                        'name' => $item->api->title,
                        'type' => $item->type ?? 0,
                        'api' => $item->api->api,
                    ];
                }
            })->keyBy('api')->toArray();
        $app_plans->costs = array_filter($apis, function ($key) {
            return ! empty($key);
        }, ARRAY_FILTER_USE_KEY);
        $app_plans->name = '基础版';
        return [
            'is_vip' => 0,
            'name' => $app_plans->name,
            'expired_at' => '',
            'costs' => $app_plans->costs,
        ];
        //        }, 20);
    }
}
if (! function_exists('get_website_update')) {
    function get_website_update($app_id, $user_info)
    {
        $info = ApplicationConfig::query()->where('app_id', $app_id)->whereRaw('JSON_CONTAINS_PATH(config_data, "one", "$.website_update")')->first();
        if (! $info) {
            return 0;
        }
        $website_update = $info->config_data['website_update'] ?? 0;
        // 注册时间在指定时间前的人不显示升级
        $user_created_time_limit = system_config('user_created_time_limit');
        $user_created_time_limit = str_replace('"', '', $user_created_time_limit);
        if ($website_update == 1 && $user_created_time_limit && $user_info->created_time && strtotime($user_info->created_time) < strtotime($user_created_time_limit)) {
            $website_update = 0;
        }
        return $website_update;
    }
}

/*
 * 查看用户是否是OA内部人员 0-不是 1-是
 */
if (! function_exists('is_inner_user')) {
    function is_inner_user($user_info)
    {
        $is_inner = 0;
        $test_mobiles = json_decode(system_config('test_mobiles'), true);
        if (in_array($user_info->phone, $test_mobiles)) {
            $is_inner = 1;
        }
        if (CorpEmployee::query()->where('corp_id', 1)->where('mobile', $user_info->phone)->count() > 0) {
            $is_inner = 1;
        }
        return $is_inner;
    }
}

/*
 * 获取用户的付费方案-新
 *
 * @param $user_id  int 用户ID
 * @param $model_id int 模型ID
 * @param $is_cache bool 是否走缓存
 *
 * @return array|mixed|object
 */
if (! function_exists('get_app_plan_new')) {
    function get_app_plan_new($app_id = null, $user_id = null, $corp_id = null, $team_corp_id = null, $is_cache = true)
    {
        //        return cache_has_set("user_app_plan:" . $user_id . ':' . $app_id, static function () use ($org_name, $app_id, $user_id, $corp_id) {
        $user_plans = [];
        $team_vip = 0; // 团队vip状态
        // $is_free = 0;//是否免费
        $expired_at = '';
        # 用户级
        if ($user_id > 0) {
            // 用户开通的付费计划
            $user_plans = UserVipPlan::query()->with('payPlans')
                ->where('is_valid', 1)
                ->where('dev_id', 0)
                ->where('user_id', $user_id)
                ->where('app_id', $app_id)
                ->where('team_corp_id', 0)
                ->orderByDesc('type')
                ->orderByDesc('id')
                ->first();
            if ($user_plans && $user_plans->expired_time instanceof Carbon && Carbon::now()->gt($user_plans->expired_time)) {
                $user_plans->is_valid = 0;
                $user_plans->expired_time = null;
                $user_plans->save();
                $user_plans = null;
            }
        }
        # 应用级
        $app_plans = PaymentPlan::query()->where('app_id', $app_id)->where('is_basis', 1)->first();

        // 都没配置报错
        if (! $user_plans) {
            p($app_id, '$app_id');
            p($user_id, '$user_id');
            p($corp_id, '$corp_id');
            p($team_corp_id, '$team_corp_id');
            throw new BusinessException('该应用暂未配置付费策略', 400);
        }
        // 1.没有用户套餐直接给应用的
        // 2.团队套餐统一走这里输出
        if (! $user_plans || empty($app_plans->payPlans)) {
            // 兼容团队vip状态
            $result = [];
            foreach ($app_plans->plans as $item) {
                $result[$item['api']] = $item;
            }
            return [
                'is_vip' => 0,
                'name' => $app_plans->name,
                'expired_at' => $expired_at,
                'costs' => $result,
            ];
        }

        # 都有的话  两者融合
        $plans_user = $user_plans->payPlans->plans;
        $plans_app = $app_plans->plans;
        foreach ($plans_app as $element) {
            $api = $element['api'];
            $found = false;

            foreach ($plans_user as $existingElement) {
                if ($existingElement['api'] === $api) {
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $plans_user[] = $element;
            }
        }
        $result = [];
        foreach ($plans_user as $item) {
            $result[$item['api']] = $item;
        }
        return [
            'is_vip' => $user_plans->expired_time instanceof Carbon ? 1 : 0,
            'name' => $user_plans->payPlans->name,
            'expired_at' => $user_plans->expired_time instanceof Carbon ? $user_plans->expired_time->format('Y-m-d H:i:s') : '',
            'costs' => $result,
        ];
        //        }, 20);
    }
}

if (! function_exists('gmt_iso8601')) {
    function gmt_iso8601($time)
    {
        return str_replace('+00:00', '.000Z', gmdate('c', $time));
    }
}

if (! function_exists('desensitize_phone')) {
    function desensitize_phone($phone)
    {
        if (! $phone) {
            return '';
        }
        // 使用正则表达式匹配手机号码的前三位和后四位
        $pattern = '/(\d{3})\d{4}(\d{4})/';
        $replacement = '$1****$2';
        // 替换手机号码中的敏感信息
        return preg_replace($pattern, $replacement, $phone);
    }
}

if (! function_exists('put_log_file')) {
    function put_log_file($data, $title = 'LOG', $name = 'log')
    {
        if (is_array($data)) {
            $data = json_encode($data, 256);
        }
        return file_put_contents(BASE_PATH . '/runtime/' . $name . '.log', '【' . $title . '】' . $data . PHP_EOL, FILE_APPEND);
    }
}

if (! function_exists('mask_email')) {
    function mask_email($email)
    {
        if (! $email) {
            return '';
        }
        $parts = explode('@', $email);
        $username = $parts[0];
        $domain = $parts[1];

        // 脱敏处理用户名部分
        $usernameLength = strlen($username);

        if ($usernameLength <= 2) {
            // 长度小于等于2的用户名，直接保留原始值
            $maskedUsername = $username;
        } else {
            // 长度大于2的用户名，保留前两个字符和最后两个字符，中间用星号代替
            $maskedUsername = substr($username, 0, 2) . str_repeat('*', max($usernameLength - 4, 0)) . substr($username, -2);
        }

        // 拼接脱敏后的邮箱地址
        return $maskedUsername . '@' . $domain;
    }
}

if (! function_exists('system_config')) {
    function system_config(string $key, mixed $default = null, int $tll = 60, bool $fresh = false)
    {
        return AdminSetting::query()->where('key', $key)->value('values') ?? $default;
        if ($fresh) {
            return AdminSetting::query()->where('key', $key)->value('values') ?? $default;
        }

        $value = cache_has_set('system_config_cache:' . $key, function () use ($key) {
            return AdminSetting::query()->where('key', $key)->value('values');
        }, $tll);

        return $value ?? $default;
    }
}

if (! function_exists('get_wallet_scope')) {
    function get_wallet_scope($wallet_type = 0)
    {
        return match ($wallet_type) {
            1 => 'Organization',
            2, 3 => 'Individual',
            4 => 'Team',
            default => '未知类型'
        };
    }
}

if (! function_exists('get_max_team')) {
    function get_max_team($corp_id = 0)
    {
        return AdminSetting::query()->where('key', 'max_team_create_num')->value('values') ?? 2;
    }
}

/*
 * 获取能加入团队的最大人数
 */
if (! function_exists('get_max_join_team_person')) {
    function get_max_join_team_person($corp_id)
    {
        return Corps::query()->where(['corp_id' => $corp_id])->value('max_person_num') ?? -1;
    }
}

/*
 * 获取团队内能享受到付费套餐的最大人数
 */
if (! function_exists('get_max_team_person')) {
    function get_max_team_person($corp_id)
    {
        $customNum = Corps::query()->where('corp_id', $corp_id)->value('max_team_vip_person_num');
        if ($customNum != -1) {
            return $customNum;
        }

        $team_plans = UserVipPlan::query()->with('payPlans')
            ->where('is_valid', 1)
            ->where('dev_id', 0)
            ->where('team_corp_id', $corp_id)
            ->orderByDesc('type')
            ->orderByDesc('id')
            ->first();
        $team_plans = $team_plans->payPlans->plans ?? null;
        if (! $team_plans) {
            $team_plans = PaymentPlan::query()->where('is_team', 1)->where('app_id', -1)->where('team_package_type', 1)->value('plans');
        }
        foreach ($team_plans as $item) {
            if ($item['api'] == 'ai.team.person.count') {
                $max_team_person = $item['dec_key_number'];
                break;
            }
        }

        return $max_team_person ?? 2;
    }
}

if (! function_exists('get_team_vip_status')) {
    function get_team_vip_status($corp_id)
    {
        // UU跑腿直接返回
        if ($corp_id == 1) {
            return 1;
        }
        $team_plans = UserVipPlan::query()
            ->where('is_valid', 1)
            ->where('team_corp_id', $corp_id)
            ->orderByDesc('type')
            ->orderByDesc('id')
            ->first();
        if (! $team_plans || ! $team_plans->expired_time) {
            return 0;
        }
        if (Carbon::now()->lt($team_plans->expired_time)) {
            return 1;
        }
        return 0;
    }
}

if (! function_exists('get_team_user_payment_type')) {
    function get_team_user_payment_type($corp_id)
    {
        // 免费版 全部走按量, 开会员以后根据席位判断
        $is_vip = get_team_vip_status($corp_id);
        if ($is_vip == 1) {
            $max_team_person = get_max_team_person($corp_id);
            $already_count = CorpEmployee::query()->whereIn('status', [0, 1])->where('corp_id', $corp_id)->count();
            $payment_type = 1; // 1跟团队所属套餐走 2按量扣
            if ($already_count >= $max_team_person) {
                $payment_type = 2;
            }
        } else {
            $payment_type = 2;
        }
        return $payment_type;
    }
}

if (! function_exists('get_team_corp_id')) {
    function get_team_corp_id($code, $app_id, $user_id)
    {
        //        if(is_int($code)){
        //            return $code;
        //        }
        //        $team_corp_id = \App\Model\Corps::query()->where('corp_code', $code)->value('corp_id');
        //        if((int)$team_corp_id <= 0){
        //            $team_corp_id = 0;
        //        }
        //        return $team_corp_id;
        if (is_numeric($code) && $code > 0) {
            return $code;
        }
        // 如果是小程序的appid,则把appid改为PC-UUAI实验室
        if ($app_id == 61) {
            $app_id = 69;
        }
        $record = CorpUserRecord::query()->where('user_id', $user_id)->where('app_id', $app_id)->first();
        return $record->team_corp_id ?? 0;
    }
}

if (! function_exists('generateUniqueString')) {
    function generateUniqueString($id)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $uniqueString = '';

        $max = strlen($characters) - 1;

        // 使用ID作为种子生成唯一字符串
        mt_srand($id);
        for ($i = 0; $i < 8; ++$i) {
            $uniqueString .= $characters[mt_rand(0, $max)];
        }

        return $uniqueString;
    }
}

if (! function_exists('add_wallet_flow')) {
    function add_wallet_flow($wallet_id, $owner, $type, $key_count, $before, $user_name, $app_id, $user_id, $remark, $scope = 'Individual', $team_corp_id = 0)
    {
        $date = date('Ymd_His');
        $random = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 6);
        UserWalletFlow::create([
            'wallet_id' => $wallet_id,
            'owner' => $owner,
            'order_sn' => $date . '_' . $random,
            'type' => $type,
            'key_count' => $key_count,
            'scope' => $scope,
            'before' => $before,
            'after' => bcadd($before . '', $key_count . '', 2),
            'price' => $key_count,
            'organization' => '2cnt1sce',
            'user' => $user_name,
            'created_time' => date('Y-m-d\TH:i:s+08:00'),
            'app_id' => $app_id,
            'user_id' => $user_id,
            'corp_id' => 86,
            'remark' => $remark,
            'team_corp_id' => $team_corp_id,
        ]);
    }
}

if (! function_exists('hide_mobile')) {
    function hide_mobile($str, $visibleCount = 4, $maskCharacter = '*')
    {
        $length = strlen($str);

        if ($length <= $visibleCount * 2) {
            return $str;  // Not enough characters to hide
        }

        $visibleStart = 0;
        $visibleEnd = $length - $visibleCount;

        $visiblePart = substr($str, $visibleStart, $visibleCount);
        $hiddenPart = str_repeat($maskCharacter, $visibleEnd - $visibleStart);

        return $visiblePart . $hiddenPart . substr($str, $visibleEnd);
    }
}

if (! function_exists('time_elapsed_string')) {
    function time_elapsed_string($datetime)
    {
        $currentTime = time();
        $timeDiff = $currentTime - strtotime($datetime);

        $seconds = $timeDiff;
        $minutes = round($seconds / 60);
        $hours = round($minutes / 60);
        $days = round($hours / 24);
        $months = round($days / 30);

        if ($seconds <= 60) {
            return $seconds . '秒前';
        }
        if ($minutes <= 60) {
            return $minutes . '分钟前';
        }
        if ($hours <= 24) {
            return $hours . '小时前';
        }
        if ($days <= 30) {
            return $days . '天前';
        }
        return $months . '个月前';
    }
}
