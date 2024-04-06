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

namespace App\Bean;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Application;
use App\Model\EicApicostLog;
use App\Model\UserWallet;
use App\Model\UserWalletFlow;
use App\Service\ApiAccountService;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Throwable;

class AiBean extends SplBean
{
    public const CONST_PAY_CONSUMER_TYPE_USER = 2;

    public const CONST_PAY_CONSUMER_TYPE_ORG = 1;

    public const CONST_PAY_CONSUMER_TYPE_DEV = 3;

    public const CONST_PAY_CONSUMER_TYPE_TEAM = 4;

    protected int $team_corp_id = 0;

    protected string $engine = '';

    protected string $tab_type = '';

    protected int $module_type = 0;

    protected int $key_id = 0;

    protected string $api_key = '';

    protected ?array $api_options = [];

    protected ?string $api = null;

    protected ?string $prompt = null;

    protected ?string $message_id = '';

    protected $user_id = '';

    protected $app_id = '';

    protected ?int $organization_id = 0;

    protected ?string $application_name = '';

    protected ?int $msg_id = 0;

    protected bool $is_vip = false;

    protected bool $is_open_chatgpt = false;

    protected array $options = [];

    protected array $images = [];

    protected array $messages = [];

    protected int $dec_key_number = 0;

    protected int $is_free = 0;

    protected int $current_balance = 0;

    protected int $pay_consumer = self::CONST_PAY_CONSUMER_TYPE_ORG;

    protected ?string $user_name = '';

    protected string $organization_name = '';

    protected string $client_id = '';

    protected string $remark = '';

    protected bool $stream = false;

    public function getKeyId(): int
    {
        if ($this->key_id) {
            return $this->key_id;
        }
        $this->getApiKey();
        return $this->key_id;
    }

    public function setKeyId(int $key_id): void
    {
        $this->key_id = $key_id;
    }

    public function getApiKey($model = null): string
    {
        if ($this->api_key) {
            return $this->api_key;
        }
        $apiAccount = ApiAccountService::getApiAccount($model ?: $this->getEngine(), $this->getAppId());
        $this->setKeyId($apiAccount->key_id);
        $this->setApiKey($apiAccount->api_key);
        $this->setApiOptions($apiAccount->extra ?? []);
        return $this->api_key;
    }

    public function setApiKey(string $api_key): void
    {
        $this->api_key = $api_key;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function setImages(array $images): void
    {
        $this->images = $images;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function setUserId($user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getOrganizationId(): ?int
    {
        return $this->organization_id;
    }

    public function setOrganizationId(?int $organization_id): void
    {
        $this->organization_id = $organization_id;
    }

    public function getApplicationName(): ?string
    {
        return $this->application_name;
    }

    public function setApplicationName(?string $application_name): void
    {
        $this->application_name = $application_name;
    }

    public function is_vip(): bool
    {
        return $this->is_vip;
    }

    public function setIsVip(bool $is_vip): void
    {
        $this->is_vip = $is_vip;
    }

    /**
     * @param mixed $isRaw
     */
    public function getEngine($isRaw = false): string
    {
        return $this->engine;
    }

    public function setEngine(string $engine): void
    {
        $this->engine = $engine;
    }

    public function getMessageId(): ?string
    {
        return $this->message_id;
    }

    public function setMessageId(?string $message_id): void
    {
        $this->message_id = $message_id;
    }

    /**
     * @param mixed $default
     */
    public function getOption(?string $name = null, $default = '')
    {
        return $this->options[$name] ?? $default;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getMsgId(): ?int
    {
        return $this->msg_id;
    }

    public function setMsgId(?int $msg_id): void
    {
        $this->msg_id = $msg_id;
    }

    public function getPrompt(): ?string
    {
        return $this->prompt;
    }

    public function setPrompt(?string $prompt): void
    {
        $this->prompt = $prompt;
    }

    public function isIsOpenChatgpt(): bool
    {
        return $this->is_open_chatgpt;
    }

    public function setIsOpenChatgpt(bool $is_open_chatgpt): void
    {
        $this->is_open_chatgpt = $is_open_chatgpt;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }

    public function getDecKeyNumber(): int
    {
        return $this->dec_key_number;
    }

    public function setDecKeyNumber(int $dec_key_number): void
    {
        $this->dec_key_number = $dec_key_number;
    }

    // 获取扣费计划
    public function checkDecKeyNumber()
    {
        $dec_key_number = $this->dec_key_number;
        $app_pay_type = Application::where('app_id', $this->getAppId())->value('pay_type'); // pay_type 计费方式 1组织 2个人 3开发者 4团队
        // 兼容团队扣费类型

        if ($dec_key_number <= 0) {
            // 新版 取出应用/个人/开发者 的扣费计划
            $results = [];
            $user_app_plan = get_app_plan_new($this->getAppId(), $this->getUserId(), $this->getOrganizationId(), $this->getTeamCorpId());
            p($this->getApi(), '前端传的扣费接口');
            if (isset($user_app_plan['costs'][$this->getApi()])) {
                $results = $user_app_plan['costs'][$this->getApi()];
                p($results, '后端查的扣费接口');
            }
            if (empty($results)) {
                throw new BusinessException(ErrorCode::ERROR_INTERNAL_API_NO_PERMISSION, "您没有接口{$this->getApi()}的权限");
            }
            $dec_key_number = intval($results['dec_key_number'] ?? 0);
        }
        //        model_limit($this->getUserId(), $results['id'], $results['qps']);

        // 根据类型 检查组织/用户的 余额 是否足够
        switch ($app_pay_type) {
            case self::CONST_PAY_CONSUMER_TYPE_ORG:
                $eicWallet = UserWallet::where('type', self::CONST_PAY_CONSUMER_TYPE_ORG)
                    ->where('corp_id', $this->getOrganizationId())->value('key_count');
                break;
            case self::CONST_PAY_CONSUMER_TYPE_USER:
                $eicWallet = UserWallet::where('type', self::CONST_PAY_CONSUMER_TYPE_USER)
                    ->where('user_id', $this->getUserId())->where('app_id', $this->getAppId())->value('key_count');
                break;
            case self::CONST_PAY_CONSUMER_TYPE_DEV:
                $eicWallet = UserWallet::where('type', self::CONST_PAY_CONSUMER_TYPE_USER)
                    ->where('user_id', $this->getUserId())->where('app_id', 28)->value('key_count');
                break;
            case self::CONST_PAY_CONSUMER_TYPE_TEAM:
                // 团队版点数 需要用 包月+通用点数之和 来确定是否充足
                $wallet = UserWallet::where('type', self::CONST_PAY_CONSUMER_TYPE_TEAM)->where('team_corp_id', $this->getTeamCorpId())
                    ->selectRaw('SUM(key_count + month_key + uu_month_key) as total')
                    ->first();
                $eicWallet = $wallet->total ?? 0;
                break;
        }
        $before = $eicWallet ?? 0;
        if ($this->isIsOpenChatgpt()) {
            ++$dec_key_number;
        }
        if (isset($this->options['num'])) {
            $dec_key_number = $dec_key_number * intval($this->options['num']);
        }
        if (isset($user_app_plan['is_vip']) && $user_app_plan['is_vip'] == 0) {
            if (isset($this->options['quality']) && $this->options['quality'] == 2) {
                $dec_key_number = $dec_key_number + 1;
            }
            if (isset($this->options['quality']) && $this->options['quality'] == 'HIGH_DEFINITION') {
                $dec_key_number = $dec_key_number + 1;
            }
        }
        if ($before < $dec_key_number) {
            p('余额不足');
            throw new BusinessException(ErrorCode::ERROR_INTERNAL_INSUFFICIENT_BALANCE);
        }
        // 临时处理 UUPT 不扣费
        if ($this->getTeamCorpId() == 1) {
            $dec_key_number = 0;
        }
        $this->setDecKeyNumber($dec_key_number);
        $this->setCurrentBalance((int) $before);
        $this->setPayConsumer($app_pay_type);
        return true;
    }

    // 扣费逻辑
    public function doDecKeyNumber()
    {
        Db::connection('casdoor')->beginTransaction();
        try {
            // 扣除key
            switch ($this->getPayConsumer()) {
                case self::CONST_PAY_CONSUMER_TYPE_ORG:
                    $wallet = UserWallet::where('type', self::CONST_PAY_CONSUMER_TYPE_ORG)
                        ->where('corp_id', $this->getOrganizationId())->first();
                    break;
                case self::CONST_PAY_CONSUMER_TYPE_USER:
                    $wallet = UserWallet::where('type', self::CONST_PAY_CONSUMER_TYPE_USER)
                        ->where('user_id', $this->getUserId())->where('app_id', $this->getAppId())->first();
                    break;
                case self::CONST_PAY_CONSUMER_TYPE_DEV:
                    $wallet = UserWallet::where('type', self::CONST_PAY_CONSUMER_TYPE_USER)
                        ->where('user_id', $this->getUserId())->where('app_id', 28)->first();
                    break;
                case self::CONST_PAY_CONSUMER_TYPE_TEAM:
                    $wallet = UserWallet::where('type', self::CONST_PAY_CONSUMER_TYPE_TEAM)
                        ->where('team_corp_id', $this->getTeamCorpId())->first();
                    break;
            }
            // 扣除流水
            $date = date('Ymd_His');
            $flow = null;
            if ($this->getDecKeyNumber() > 0) {
                $random = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 6);
                if (! empty($this->getRemark())) {
                    $remark = $this->getRemark();
                } else {
                    $remark = '使用' . $this->getEngine();
                }
                if ($this->getTeamCorpId() > 0 && ($wallet->month_key > 0 || $wallet->uu_month_key > 0)) {
                    $before = $wallet->month_key + $wallet->uu_month_key + $wallet->key_count;
                    $flow = UserWalletFlow::create([
                        'wallet_id' => $wallet->id,
                        'owner' => $this->getUserName(),
                        'order_sn' => $date . '_' . $random,
                        'type' => 'Expend',
                        'key_count' => $this->getDecKeyNumber(),
                        'scope' => get_wallet_scope($this->getPayConsumer()),
                        'before' => $before,
                        'after' => bcsub($before . '', $this->getDecKeyNumber() . '', 2),
                        'price' => $this->getDecKeyNumber(),
                        'organization' => $this->getOrganizationName(),
                        'user' => $this->getUserName(),
                        'created_time' => date('Y-m-d\TH:i:s+08:00'),
                        'app_id' => $this->getAppId(),
                        'user_id' => $this->getUserId(),
                        'corp_id' => $this->getOrganizationId(),
                        'team_corp_id' => $this->getTeamCorpId(),
                        'remark' => $remark,
                    ]);
                    // 团队版扣点逻辑 优先级 uu_month_key > month_key > key_count
                    $deduction = $this->getDecKeyNumber();
                    if ($wallet->uu_month_key >= $deduction) {
                        $wallet->uu_month_key -= $deduction;
                    } elseif ($wallet->uu_month_key > 0) {
                        $deduction -= $wallet->uu_month_key;
                        $wallet->uu_month_key = 0;

                        if ($wallet->month_key >= $deduction) {
                            $wallet->month_key -= $deduction;
                        } else {
                            $deduction -= $wallet->month_key;
                            $wallet->month_key = 0;

                            if ($wallet->key_count >= $deduction) {
                                $wallet->key_count -= $deduction;
                            } else {
                                throw new BusinessException(ErrorCode::ERROR_INTERNAL_INSUFFICIENT_BALANCE);
                            }
                        }
                    } else {
                        if ($wallet->month_key >= $deduction) {
                            $wallet->month_key -= $deduction;
                        } else {
                            $deduction -= $wallet->month_key;
                            $wallet->month_key = 0;

                            if ($wallet->key_count >= $deduction) {
                                $wallet->key_count -= $deduction;
                            } else {
                                throw new BusinessException(ErrorCode::ERROR_INTERNAL_INSUFFICIENT_BALANCE);
                            }
                        }
                    }
                    $wallet->save();
                } else {
                    $flow = UserWalletFlow::create([
                        'wallet_id' => $wallet->id,
                        'owner' => $this->getUserName(),
                        'order_sn' => $date . '_' . $random,
                        'type' => 'Expend',
                        'key_count' => $this->getDecKeyNumber(),
                        'scope' => get_wallet_scope($this->getPayConsumer()),
                        'before' => $wallet->key_count,
                        'after' => bcsub($wallet->key_count . '', $this->getDecKeyNumber() . '', 2),
                        'price' => $this->getDecKeyNumber(),
                        'organization' => $this->getOrganizationName(),
                        'user' => $this->getUserName(),
                        'created_time' => date('Y-m-d\TH:i:s+08:00'),
                        'app_id' => $this->getAppId(),
                        'user_id' => $this->getUserId(),
                        'corp_id' => $this->getOrganizationId(),
                        'team_corp_id' => $this->getTeamCorpId(),
                        'remark' => $remark,
                    ]);
                    $wallet->decrement('key_count', $this->getDecKeyNumber());
                }
                $wallet->save();
            }
            // 新增调用记录
            EicApicostLog::create([
                'cost' => $this->getDecKeyNumber(),
                'before' => $this->getCurrentBalance(),
                'after' => $this->getCurrentBalance() - $this->getDecKeyNumber(),
                'client_id' => $this->getClientId(),
                'organization' => $this->getOrganizationName(),
                'scope' => $this->getPayConsumer(),
                'api' => $this->getApi(),
                'user' => $this->getUserName(),
                'app_id' => $this->getAppId(),
                'user_id' => $this->getUserId(),
                'corp_id' => $this->getOrganizationId(),
                'team_corp_id' => $this->getTeamCorpId(),
                'created_time' => Carbon::now(),
            ]);
            Db::connection('casdoor')->commit();
            return $flow->order_sn ?? '';
        } catch (Throwable $ex) {
            Db::connection('casdoor')->rollBack();
            p($ex->getMessage(), '扣费逻辑失败');
            throw new BusinessException(ErrorCode::ERROR_INTERNAL_DEDUCT_FEE);
        }
    }

    public function getStream(): bool
    {
        return $this->stream;
    }

    public function setStream(bool $stream): void
    {
        $this->stream = $stream;
    }

    public function getApi(): ?string
    {
        if (empty($this->api)) {
            $pattern = '/^gpt-(3\.5|4)/';
            if (preg_match($pattern, $this->engine, $matches)) {
                return $matches[0]; // 输出 gpt-4
            }
            return $this->engine; // 输出 gpt-4-turbo
        }
        return $this->api;
    }

    public function setApi(?string $api): void
    {
        $this->api = $api;
    }

    public function getCurrentBalance(): int
    {
        return $this->current_balance;
    }

    public function setCurrentBalance(int $current_balance): void
    {
        $this->current_balance = $current_balance;
    }

    public function getPayConsumer(): int
    {
        return $this->pay_consumer;
    }

    public function setPayConsumer(int $pay_consumer): void
    {
        $this->pay_consumer = $pay_consumer;
    }

    public function getOrganizationName(): string
    {
        return $this->organization_name;
    }

    public function setOrganizationName(string $organization_name): void
    {
        $this->organization_name = $organization_name;
    }

    public function getUserName(): ?string
    {
        return $this->user_name;
    }

    public function setUserName(?string $user_name): void
    {
        $this->user_name = $user_name;
    }

    public function getClientId(): string
    {
        return $this->client_id;
    }

    public function setClientId(string $client_id): void
    {
        $this->client_id = $client_id;
    }

    public function getAppId()
    {
        // 如果是小程序的appid,则把appid改为PC-UUAI实验室
        // 此处适用于AiBean类扣费逻辑
        if ($this->app_id == 61) {
            return 69;
        }
        return $this->app_id;
    }

    public function setAppId($app_id): void
    {
        $this->app_id = $app_id;
    }

    public function getApiOptions(): array
    {
        return $this->api_options;
    }

    /**
     * @param mixed $key
     * @param mixed $default
     * @return array
     */
    public function getApiOption($key = '', $default = '')
    {
        return $this->api_options[$key] ?? $default;
    }

    public function setApiOptions(?array $api_options): void
    {
        $this->api_options = $api_options;
    }

    public function getTabType(): string
    {
        return $this->tab_type;
    }

    public function setTabType(string $tab_type): void
    {
        $this->tab_type = $tab_type;
    }

    public function getModuleType(): int
    {
        return $this->module_type;
    }

    public function setModuleType(int $module_type): void
    {
        $this->module_type = $module_type;
    }

    public function getRemark(): string
    {
        return $this->remark;
    }

    public function setRemark(string $remark): void
    {
        $this->remark = $remark;
    }

    public function getIsFree(): int
    {
        return $this->is_free;
    }

    public function setIsFree(int $is_free): void
    {
        $this->is_free = $is_free;
    }

    public function getTeamCorpId(): int
    {
        return $this->team_corp_id;
    }

    public function setTeamCorpId(int $team_corp_id): void
    {
        $this->team_corp_id = $team_corp_id;
    }
}
