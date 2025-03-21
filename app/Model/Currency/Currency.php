<?php

declare(strict_types=1);

namespace App\Model\Currency;

use App\QueryBuilder\Model;
use Carbon\Carbon;

/**
 * @property int $id 币种ID，主键
 * @property array $name 币种名称（支持多语言）
 * @property string $symbol 币种代码
 * @property string|null $icon 币种图标
 * @property int $sort 排序
 * @property int $decimals 精度
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 */
final class Currency extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'currency';

    /**
     * The connection name for the model.
     */
    // protected ?string $connection = 'default';

    /**
     * The attributes that are mass assignable.
     */
    // protected array $fillable = [
    //     'name',
    //     'symbol',
    //     'icon',
    //     'sort',
    //     'decimals',
    // ];

    protected array $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'id' => 'integer',
        'name' => 'json',
        'decimals' => 'integer',
        'sort' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
