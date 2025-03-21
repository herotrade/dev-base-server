<?php

declare(strict_types=1);
/**
 * 策略平台API
 * 币种服务类
 */

namespace App\Http\Admin\Service\Currency;

use App\Model\Currency\Currency;
use App\QueryBuilder\QueryBuilder;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Container\ContainerInterface;

class CurrencyService
{
    public function __construct(private readonly ContainerInterface $container) {}

    /**
     * 获取币种列表
     */
    public function list(RequestInterface $request): mixed
    {
        return QueryBuilder::for(Currency::class, $request)
            ->filters(['symbol', 'name'])
            ->defaultSort('sort')
            ->allowedSorts(['id', 'sort', 'created_at'])
            ->pagex();
    }

    /**
     * 获取币种详情
     */
    public function show(int $id): Currency
    {
        return Currency::query()->findOrFail($id);
    }

    /**
     * 创建币种
     */
    public function create(array $data): Currency
    {
        return Currency::query()->create($data);
    }

    /**
     * 更新币种
     */
    public function update(int $id, array $data): Currency
    {
        $currency = Currency::query()->findOrFail($id);
        $currency->update($data);
        return $currency;
    }

    /**
     * 删除币种
     */
    public function delete(int $id): bool
    {
        $currency = Currency::query()->findOrFail($id);
        return $currency->delete();
    }

    /**
     * 批量删除币种
     */
    public function batchDelete(array $ids)
    {
        return Currency::query()->whereIn('id', $ids)->delete();
    }
}
