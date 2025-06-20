<?php

use Hyperf\Context\ApplicationContext;
use App\Logger\LoggerFactory;

if (! function_exists('app')) {
    /**
     * 获取容器实例
     * @return \Psr\Container\ContainerInterface
     */
    function app(): \Psr\Container\ContainerInterface
    {
        return container();
    }
}

if (! function_exists('container')) {
    /**
     * 获取容器实例
     * @return \Psr\Container\ContainerInterface
     */
    function container(): \Psr\Container\ContainerInterface
    {
        return ApplicationContext::getContainer();
    }
}

if (! function_exists('redis')) {
    /**
     * 获取Redis实例
     * @return \Hyperf\Redis\Redis
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function redis(): \Hyperf\Redis\Redis
    {
        return container()->get(\Hyperf\Redis\Redis::class);
    }
}

if (! function_exists('logger')) {

    /**
     * 获取日志实例 App\Logger\LoggerFactory 支持设置日志文件相对目录和日志文件名
     * 当参数以 ".log" 结尾时表示设置 $fileName 日志将写入到自定义的日志文件中
     * logger($name|$fileName) 一个参数时
     * logger($name, $group|$fileName) 两个参数时
     * logger($name, $group, $fileName) 三个参数时
     * @param string $name
     * @return \Hyperf\Logger\Logger
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function logger(...$args): \Hyperf\Logger\Logger
    {
        $logger = container()->get(LoggerFactory::class);

        if (count($args) == 1) {
            if (str_contains($args[0], '.log')) {
                $name = str_replace('.log', '', $args[0]);
                if (str_starts_with($name, '/')) {
                    $name = substr($name, 1);
                }
                $name = str_replace('/', ':', $name);
                return $logger->fileName($args[0])->get($name);
            } else {
                return $logger->get($args[0]);
            }
        }

        if (count($args) == 2) {
            if (str_contains($args[1], '.log')) {
                $name = str_replace('.log', '', $args[1]);
                if (str_starts_with($name, '/')) {
                    $name = substr($name, 1);
                }
                $name = $args[0] . ':' . str_replace('/', ':', $name);
                return $logger->fileName($args[1])->get($name);
            } else {
                return $logger->get($args[0], $args[1]);
            }
        }

        if (count($args) >= 3) {
            if (!str_contains($args[2], '.log')) {
                $args[2] = $args[2] . '.log';
            }

            $name = str_replace('.log', '', $args[2]);
            if (str_starts_with($name, '/')) {
                $name = substr($name, 1);
            }
            $name = $args[0] . ':' . str_replace('/', ':', $name);

            return $logger->fileName($args[2])->get($name, $args[1]);
        }

        return $logger->get();
    }
}

if (! function_exists('format_size')) {
    /**
     * 格式化大小
     * @param int $size
     * @return string
     */
    function format_size(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $index = 0;
        for ($i = 0; $size >= 1024 && $i < 5; $i++) {
            $size /= 1024;
            $index = $i;
        }
        return round($size, 2) . $units[$index];
    }
}

if (! function_exists('context_set')) {
    /**
     * 设置上下文数据
     * @param string $key
     * @param $data
     * @return bool
     */
    function context_set(string $key, $data): bool
    {
        return (bool)\Hyperf\Context\Context::set($key, $data);
    }
}

if (! function_exists('context_get')) {
    /**
     * 获取上下文数据
     * @param string $key
     * @return mixed
     */
    function context_get(string $key)
    {
        return \Hyperf\Context\Context::get($key);
    }
}

if (! function_exists('client')) {
    /**
     * 获取HTTP客户端实例
     * @return \GuzzleHttp\Client
     */
    function client(array $config = []): \GuzzleHttp\Client
    {
        // 设置代理
        if (env('REQUEST_PROXY')) {
            $config['proxy'] = [
                'http' => env('HTTP_PROXY'),
                'https' => env('HTTPS_PROXY', env('HTTP_PROXY')),
            ];
        }
        return new \GuzzleHttp\Client($config);
    }
}


if (! function_exists('configValue')) {
    /**
     * 获取配置值
     * Summary of configValue
     * @param mixed $configStr 格式如 partner.cn
     * @return GuzzleHttp\Client
     */
    function configValue($configValue): String
    {
        $configRemark = '';
        $codeArray = explode('.', $configValue);
        $partner = \Plugin\West\SysSettings\Helper\Helper::getSysSettingType($codeArray[0]);
        foreach ($partner['info'] as $key => $value) {
            if (strpos($value, $codeArray[1]) !== false) {
                $configRemark = $value['value'];
                break;
            }
        }
        return $configRemark;
    }
}

if (!function_exists('publishMessage')) {
    /**
     * 发布消息（向指定频道发布 Redis 订阅消息）
     * @param string $channel
     * @param string|array $data
     * @param int|array|null $userId 指定用户ID（可指定多个）
     * @return void
     */
    function publishMessage(string $channel, string|array $data, $userId = null): void
    {
        if (is_string($data)) {
            $dataArr = json_decode($data, true);
            if (is_array($dataArr)) {
                $data = $dataArr;
            }
        }

        // 发布数据格式
        // $message = [
        //     'user_id' => 1, // 如果存在此字段说明需要发布给指定的用户（可传入 id 数组指定多个用户）
        //     'data' => [] // 需要发布的数据
        // ];

        $message = [
            'data' => $data,
        ];
        if ($userId) {
            if (is_array($userId)) {
                $message['user_ids'] = $userId;
            } else {
                $message['user_id'] = $userId;
            }
        }

        redis()->publish($channel, json_encode($message));
    }
}


if (! function_exists('getClientIp')) {
    /**
     * Summary of getClientIp
     * @param mixed $request
     * @return string
     */
    function getClientIp($request): String
    {
        $ip = $request->getServerParams()['remote_addr'] ?? '0.0.0.0';

        if (isset($request->getServerParams()['http_x_forwarded_for'])) {
            $forwardedIps = explode(',', $request->getServerParams()['http_x_forwarded_for']);
            $ip = trim($forwardedIps[0]);
        }
        return $ip;
    }
}


if (!function_exists('getIpAddress')) {
    function getIpAddress($ip)
    {
        $ch = curl_init();
        // 设置URL和相应的选项
        curl_setopt($ch, CURLOPT_URL, "https://ipinfo.io/" . $ip);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 将curl_exec()获取的信息以字符串返回，而不是直接输出。
        // 执行并获取返回内容
        $response = curl_exec($ch);
        // 检查是否有错误发生
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);
        // 解析响应的JSON数据
        $data = json_decode($response, true);
        return [
            'country' => $data['country'] ?? null,
            'region' => $data['region'] ?? null,
            'city' => $data['city'] ?? null,
        ];
    }
}
