<?php


namespace App\QueryBuilder;

use ApiElf\QueryBuilder\AllowedFilter;
use Hyperf\HttpServer\Contract\RequestInterface;

class QueryBuilder extends \ApiElf\QueryBuilder\QueryBuilder
{
    /**
     * 默认加入时间、id 的过滤器
     */
    public function filters($filters): self
    {
        $filters = is_array($filters) ? $filters : func_get_args();
        $filters = array_merge(
            [
                AllowedFilter::exact('id'),

                // 需要在模型中 use App\QueryBuilder\Traits\TimeScope;  已经在 App\QueryBuilder\Model 中引入 继承 App\QueryBuilder\Model 的模型均可直接使用
                AllowedFilter::scope('CreatedAtOfDay'),
                AllowedFilter::scope('CreatedAtBefore'),
                AllowedFilter::scope('CreatedAtAfter'),
                AllowedFilter::scope('CreatedAtBetween'),
                AllowedFilter::scope('UpdatedAtBefore'),
                AllowedFilter::scope('UpdatedAtAfter'),
                AllowedFilter::scope('UpdatedAtBetween'),

            ],
            $filters
        );
        return parent::allowedFilters($filters);
    }

    /**
     * 分页 允许通过 query 参数 page_size 设置为 -1 获取所有记录
     */
    public function pagex(?int $pageSize = null)
    {
        // dump($this->request->request()->all());
        if (is_null($pageSize) && $this->request->request()->input('page_size') == -1) {
            // query 参数 page_size = -1 表示获取所有记录
            return $this->get();
        }
        $pageSize = $pageSize ?? $this->request->request()->input('page_size', 15);
        return $this->paginate((int) $pageSize);
    }

    /**
     * 分页
     */
    public function page(?int $pageSize = null)
    {
        $pageSize = $pageSize ?? $this->request->request()->input('page_size', 15);
        $pageSize = $pageSize < 0 ? 0 : $pageSize;
        return $this->paginate((int) $pageSize);
    }
}
