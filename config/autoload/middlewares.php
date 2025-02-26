<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq

 */
use Hyperf\Validation\Middleware\ValidationMiddleware;
use Mine\Support\Middleware\CorsMiddleware;
use Mine\Support\Middleware\RequestIdMiddleware;
use Mine\Support\Middleware\TranslationMiddleware;

return [
    'http' => [
        \App\Http\Common\Middleware\CorsMiddleware::class,
        // 请求ID中间件
        RequestIdMiddleware::class,
        // 多语言识别中间件
        TranslationMiddleware::class,
        // 验证器中间件,处理 formRequest 验证器
        ValidationMiddleware::class
    ],
];
