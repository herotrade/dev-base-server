<?php

declare(strict_types=1);

namespace App\QueryBuilder;

use App\QueryBuilder\Trait\TimeScope;

class Model extends \Hyperf\DbConnection\Model\Model
{
    use TimeScope;

    public static function new()
    {
        return new static();
    }

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public static function getMorphClassName()
    {
        return with(new static)->getMorphClass();
    }

    // protected function boot(): void
    // {
    //     parent::boot();
    // }
}
