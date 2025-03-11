<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq

 */

use App\Service\PassportService;
use Mine\JwtAuth\Interfaces\CheckTokenInterface;
use Mine\Upload\Factory;
use Mine\Upload\UploadInterface;

return [
    UploadInterface::class => Factory::class,
    CheckTokenInterface::class => PassportService::class,
    // 重写一下 BootSwaggerListener，解决 Swagger 服务关闭后无法加载 Swagger 路由的问题，因为部分扩展使用的是 Swagger 的注解路由
    Hyperf\Swagger\Listener\BootSwaggerListener::class => App\Listener\BootSwaggerListener::class,
];
