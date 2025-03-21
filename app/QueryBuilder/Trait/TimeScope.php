<?php


namespace App\QueryBuilder\Trait;


use Carbon\Carbon;
use Hyperf\Database\Model\Builder;


trait TimeScope
{
    public function scopeCreatedAtOfDay(Builder $query, $data)
    {
        return $query->whereBetween(
            'created_at',
            [Carbon::parse($data)->startOfDay(), Carbon::parse($data)->endOfDay()]
        );
    }

    public function scopeCreatedAtBefore(Builder $query, $date): Builder
    {
        return $query->where('created_at', '<=', Carbon::parse($date));
    }

    public function scopeCreatedAtAfter(Builder $query, $date): Builder
    {
        return $query->where('created_at', '>=', Carbon::parse($date));
    }

    public function scopeCreatedAtBetween(Builder $query, $start, $end): Builder
    {

        return $query->whereBetween('created_at', [$start, $end]);
    }

    public function scopeUpdatedAtBefore(Builder $query, $date): Builder
    {
        return $query->where('updated_at', '<=', Carbon::parse($date));
    }

    public function scopeUpdatedAtAfter(Builder $query, $date): Builder
    {
        return $query->where('updated_at', '>=', Carbon::parse($date));
    }

    public function scopeUpdatedAtBetween(Builder $query, $start, $end): Builder
    {
        return $query->whereBetween('updated_at', [$start, $end]);
    }
}
