<?php

declare(strict_types=1);
/**
 * 策略平台API
 * 交易对资源类
 */

namespace App\Http\Api\Resource\Currency;

use App\Http\Common\Resource\BaseResource;

class CurrencyResource extends BaseResource
{
    /**
     * 转换资源为数组
     */
    public function toArray(): array
    {
        // 单条资源
        // $this->resource;
        // 详细参考 Hyperf\Resource\Json\JsonResource

        return parent::toArray();
    }
}
