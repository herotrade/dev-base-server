<?php

/**
 * CorsMiddleware.php
 * Author:chenmaq (machen7408@gmail.com)
 * Contact:tg:@chenmaq
 * Version:1.0
 * Date:2025/2/26
 * Website:web3world.blog ｜ bbbtrade.net
 * 盗版可耻，尊重正版
 * 长期更新开发，请认准正版演示产品
 */

namespace App\Http\Common\Middleware;

use Hyperf\Context\Context;
use Hyperf\HttpServer\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = Context::get(ResponseInterface::class);
        $response = $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Headers', '*')
            ->withHeader('Access-Control-Allow-Methods', '*');

        Context::set(ResponseInterface::class, $response);

        if ($request->getMethod() == 'OPTIONS') {
            return $response;
        }

        return $handler->handle($request);
    }
}