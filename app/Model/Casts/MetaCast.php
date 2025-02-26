<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq

 */

namespace App\Model\Casts;

use App\Model\Permission\Meta;
use Hyperf\Codec\Json;
use Hyperf\Contract\CastsAttributes;
use Hyperf\DbConnection\Model\Model;

class MetaCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): Meta
    {
        return new Meta(empty($value) ? [] : Json::decode($value));
    }

    /**
     * @param Meta $value
     * @param Model $model
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return Json::encode($value);
    }
}
