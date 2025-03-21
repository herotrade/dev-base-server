<?php

declare(strict_types=1);
/**
 * 策略平台API
 * 交易对控制器
 */

namespace App\Http\Api\Controller\Currency;

use App\Http\Api\Middleware\TokenMiddleware;
use App\Http\Api\Resource\Currency\CurrencyResource;
use App\Http\Api\Service\Currency\CurrencyService;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Result;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller(prefix: 'api/currency')]
#[Middleware(middleware: TokenMiddleware::class, priority: 100)]
class CurrencyController extends AbstractController
{
    #[Inject]
    protected CurrencyService $currencyService;

    /**
     * 获取交易对列表
     */
    #[GetMapping('list')]
    public function list(RequestInterface $request): Result
    {
        $result = $this->currencyService->list($request);
        return $this->success(CurrencyResource::collection($result));
    }
}
