<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq
 
 */

namespace App\Http\Common\Swagger;

use Hyperf\Swagger\Annotation as OA;

#[OA\OpenApi(
    openapi: '3.0.0',
    info: new OA\Info(
        version: '3.0.0',
        description: 'AlgoQuant 是一款基于 Hyperf 开发的开源管理系统，提供了用户管理、权限管理、系统设置、系统监控等功能。',
        title: 'AlgoQuant',
        termsOfService: 'https://www.algoquant.pro',
        contact: new OA\Contact(name: 'AlgoQuant', url: 'https://www.algoquant.pro/about'),
        license: new OA\License(name: 'Apache2.0', url: 'https://github.com/AlgoQuant/AlgoQuant/blob/master/LICENSE')
    ),
    servers: [
        new OA\Server(
            url: 'http://127.0.0.1:9501',
            description: '本地服务'
        ),
        new OA\Server(
            url: 'https://demo.AlgoQuant.com',
            description: '演示服务',
        ),
    ],
    externalDocs: new OA\ExternalDocumentation(description: '开发文档', url: 'https://v3.doc.algoquant.pro')
)]
#[OA\SecurityScheme(
    securityScheme: 'Bearer',
    type: 'http',
    name: 'Authorization',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]
#[OA\SecurityScheme(
    securityScheme: 'ApiKey',
    type: 'apiKey',
    name: 'token',
    in: 'header'
)]
final class Server {}
