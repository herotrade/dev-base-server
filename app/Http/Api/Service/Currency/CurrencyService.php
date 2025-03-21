<?php

declare(strict_types=1);
/**
 * 策略平台API
 * 交易对服务类
 */

namespace App\Http\Api\Service\Currency;

use App\Model\Currency\Currency;
use App\QueryBuilder\QueryBuilder;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Container\ContainerInterface;

class CurrencyService
{
    /**
     * 获取交易对列表
     */
    public function list(RequestInterface $request): mixed
    {
        return QueryBuilder::for(Currency::class, $request)
            ->filters(['symbol', 'name'])
            ->defaultSort('sort')
            ->allowedSorts(['id', 'sort', 'created_at'])
            ->pagex();
    }
}
