<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq

 */

use Hyperf\Framework\Bootstrap\PipeMessageCallback;
use Hyperf\Framework\Bootstrap\WorkerExitCallback;
use Hyperf\Framework\Bootstrap\WorkerStartCallback;
use Hyperf\Server\Event;
use Hyperf\Server\Server;
use Swoole\Constant;

return [
    'mode' => \SWOOLE_PROCESS,
    'servers' => [
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9501,
            'sock_type' => \SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
        ],
    ],
    'settings' => [
        // 对外部可以直接访问的目录地址，建议使用nginx反向代理访问
        Constant::OPTION_DOCUMENT_ROOT => BASE_PATH . '/storage',
        // 开启外部可以访问
        Constant::OPTION_ENABLE_STATIC_HANDLER => true,
        Constant::OPTION_ENABLE_COROUTINE => true,
        Constant::OPTION_WORKER_NUM => env('APP_DEBUG') ? 1 : swoole_cpu_num(),
        Constant::OPTION_PID_FILE => BASE_PATH . '/runtime/hyperf.pid',
        Constant::OPTION_OPEN_TCP_NODELAY => true,
        Constant::OPTION_MAX_COROUTINE => 100000,
        Constant::OPTION_OPEN_HTTP2_PROTOCOL => true,
        Constant::OPTION_MAX_REQUEST => 100000,
        Constant::OPTION_UPLOAD_MAX_FILESIZE => 10 * 1024 * 1024,
        Constant::OPTION_HTTP_AUTOINDEX => true,
        Constant::OPTION_HTTP_INDEX_FILES => ['index.html'],
        Constant::OPTION_SOCKET_BUFFER_SIZE => 3 * 1024 * 1024,
        // 关闭buffer输出大小限制
        // Constant::OPTION_BUFFER_OUTPUT_SIZE     => 3 * 1024 * 1024,
        // 上传最大为4M
        Constant::OPTION_PACKAGE_MAX_LENGTH => 4 * 1024 * 1024,
        // 设置task worker数量
        Constant::OPTION_TASK_WORKER_NUM => swoole_cpu_num(),
        // 因为 `Task` 主要处理无法协程化的方法，所以这里推荐设为 `false`，避免协程下出现数据混淆的情况
        Constant::OPTION_TASK_ENABLE_COROUTINE => false,
    ],
    'callbacks' => [
        Event::ON_WORKER_START => [WorkerStartCallback::class, 'onWorkerStart'],
        Event::ON_PIPE_MESSAGE => [PipeMessageCallback::class, 'onPipeMessage'],
        Event::ON_WORKER_EXIT => [WorkerExitCallback::class, 'onWorkerExit'],
        // Task callbacks
        Event::ON_TASK => [Hyperf\Framework\Bootstrap\TaskCallback::class, 'onTask'],
        Event::ON_FINISH => [Hyperf\Framework\Bootstrap\FinishCallback::class, 'onFinish'],
    ],
];
