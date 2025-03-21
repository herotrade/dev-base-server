<?php

declare(strict_types=1);
/**
 * 基础资源类
 */

namespace App\Http\Common\Resource;

use Hyperf\Resource\Json\JsonResource;

class BaseResource extends JsonResource
{
    /**
     * 转换资源为数组
     */
    public function toArray(): array
    {
        return parent::toArray();
    }
}
