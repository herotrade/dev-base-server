<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq

 */

use Symfony\Component\Finder\Finder;

return [
    // 修改为仅控制是否开启 Swagger 服务（当开启 Swagger 服务时，注解路由会自动开启不再受 route 配置影响）
    'enable' => false,
    // 新增控制是否开启 Swagger 注解路由（当开启 Swagger 服务时（enable = true），注解路由会自动开启不再受此配置影响）
    'route' => true,
    'port' => 9503,
    'json_dir' => BASE_PATH . '/storage/swagger',
    'html' => BASE_PATH . '/storage/swagger/index.html',
    'url' => '/swagger',
    'auto_generate' => false,
    'scan' => [
        'paths' => Finder::create()
            ->in([BASE_PATH . '/app/Http', BASE_PATH . '/app/Schema'])
            ->name('*.php')
            ->getIterator(),
    ],
    'processors' => [],
];
