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

namespace App\Kernel;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Robot
{
    /**
     * @AsyncQueueMessage()
     * @param array $at
     * @param false $isAtAll
     * @param mixed $data
     *
     * @throws GuzzleException
     */
    public static function notice($data, $at = [], $isAtAll = false)
    {
        $secret = '';
        $access_token = '';
        $time = time() * 1000;
        $stringToSign = "{$time}\n{$secret}";
        $sign = utf8_encode(urlencode(base64_encode(hash_hmac('sha256', $stringToSign, $secret, true))));

        $notice_url = "/robot/send?access_token={$access_token}&timestamp={$time}&sign={$sign}";
        $client = new Client([
            'base_uri' => 'https://oapi.dingtalk.com/',
            'timeout' => 3.0,
        ]);
        $markdown_array = [
            'msgtype' => 'markdown',
            'markdown' => $data,
            'at' => ['atMobiles' => $at, 'isAtAll' => $isAtAll],
        ];

        $client->request('POST', $notice_url, [
            'headers' => ['Content-Type' => 'application/json;charset=utf-8'],
            'json' => $markdown_array,
            'verify' => false,
        ]);
    }

    public static function notice_dev($data, $at = [], $isAtAll = false)
    {
        $secret = '';
        $access_token = '';
        $time = time() * 1000;
        $stringToSign = "{$time}\n{$secret}";
        $sign = utf8_encode(urlencode(base64_encode(hash_hmac('sha256', $stringToSign, $secret, true))));

        $notice_url = "/robot/send?access_token={$access_token}&timestamp={$time}&sign={$sign}";
        $client = new Client([
            'base_uri' => 'https://oapi.dingtalk.com/',
            'timeout' => 3.0,
        ]);
        $markdown_array = [
            'msgtype' => 'markdown',
            'markdown' => $data,
            'at' => ['atMobiles' => $at, 'isAtAll' => $isAtAll],
        ];
        $client->request('POST', $notice_url, [
            'headers' => ['Content-Type' => 'application/json;charset=utf-8'],
            'json' => $markdown_array,
            'verify' => false,
        ]);
    }

    public static function sendOrder($content = '', $type = '交易通知', $user = 'system', $money = '0')
    {
        $date = date('Y-m-d H:i:s');
        $message = '';
        $at = [
            //  '18538710107', // 王晓婷
        ];
        foreach ($at as $v) {
            $message .= ' @' . $v;
        }
        $message .= " \n\n **用户:** {$user} \n\n **商品：** {$type} \n\n **充值金额：** {$money} \n\n **发生时间：** {$date}";
        self::notice([
            'title' => "{$type} 通知",
            'text' => "### 交易通知\n\n{$message}\n\n{$content}",
        ], $at);
    }

    public static function send($content = '', $type = '调试通知')
    {
        $date = date('Y-m-d H:i:s');
        $at = [
            //  '18538710107', // 王晓婷
        ];
        if (is_array($content) && $type != '后台安全事件') {
            $content = json_encode($content, 256);
        }
        if (is_array($content)) {
            $txt = '';
            foreach ($content as $tt => $vv) {
                $txt .= $tt . '：' . $vv . "\n\n";
            }
            $content = $txt;
        }
        self::notice([
            'title' => "{$type}",
            'text' => "### {$type}\n\n{$content}",
        ], $at);
    }

    public static function sendDev($content = '', $type = '调试通知')
    {
        $date = date('Y-m-d H:i:s');
        $message = '';
        $at = [
            //  '18538710107', // 王晓婷
        ];
        if (is_array($content)) {
            $content = json_encode($content, 256);
        }
        self::notice_dev([
            'title' => "{$type}",
            'text' => "### {$type}\n\n{$content}",
        ], $at);
    }

    public static function reply($content = '', $type = '调试通知', $sessionWebhook = '', $atUsers = [])
    {
        if (is_array($content)) {
            $content = json_encode($content, 256);
        }
        //        $time = time() * 1000;
        //        $stringToSign = "{$time}\n{$secret}";
        //        $sign = utf8_encode(urlencode(base64_encode(hash_hmac("sha256", $stringToSign, $secret, true))));
        //        $notice_url = "/robot/send?access_token=$access_token&timestamp={$time}&sign={$sign}";
        $client = new Client([
            'base_uri' => 'https://oapi.dingtalk.com/',
            'timeout' => 3.0,
        ]);

        $markdown_array = [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => "{$type}",
                'text' => "### {$type}\n\n{$content}",
            ],
        ];

        if (! empty($atUsers)) {
            $markdown_array['at'] = [
                'atDingtalkIds' => $atUsers,
                'isAtAll' => false,
            ];
        }
        $client->request('POST', $sessionWebhook, [
            'headers' => ['Content-Type' => 'application/json;charset=utf-8'],
            'json' => $markdown_array,
            'verify' => false,
        ]);
    }
}
