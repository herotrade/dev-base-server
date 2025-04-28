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
    public function pagex(...$args)
    {
        $pageSize = null;
        $callback = null;

        if (!empty($args)) {
            if (is_int($args[0])) {
                $pageSize = $args[0];
            }

            if (!empty($args[1]) && is_callable($args[1])) {
                $callback = $args[1];
            }

            if (is_callable($args[0])) {
                $callback = $args[0];
            }

            if (!empty($args[1]) && is_int($args[1])) {
                $pageSize = $args[1];
            }
        }

        // dump($this->request->request()->all());
        if (is_null($pageSize) && $this->request->request()->input('page_size') == -1) {
            // query 参数 page_size = -1 表示获取所有记录
            // 注意：如果使用 get() 获取所有记录，则不会进行分页，也不会进行回调处理
            return $this->get();
        }
        $pageSize = $pageSize ?? $this->request->request()->input('page_size', 15);

        $res = $this->paginate((int) $pageSize);

        // 如果 callback 不为空，则对结果进行回调处理，通常在需要对每条数据的数据结构进行调整时使用
        if (!is_null($callback)) {
            $res->map(function ($item) use ($callback) {
                return $callback($item);
            });
        }
        return $res;
    }

    /**
     * 分页
     */
    public function page(...$args)
    {
        $pageSize = null;
        $callback = null;

        if (!empty($args)) {
            if (is_int($args[0])) {
                $pageSize = $args[0];
            }

            if (!empty($args[1]) && is_callable($args[1])) {
                $callback = $args[1];
            }

            if (is_callable($args[0])) {
                $callback = $args[0];
            }

            if (!empty($args[1]) && is_int($args[1])) {
                $pageSize = $args[1];
            }
        }

        $pageSize = $pageSize ?? $this->request->request()->input('page_size', 15);
        $pageSize = $pageSize < 0 ? 0 : $pageSize;

        $res = $this->paginate((int) $pageSize);

        // 如果 callback 不为空，则对结果进行回调处理，通常在需要对每条数据的数据结构进行调整时使用
        if (!is_null($callback)) {
            $res->map(function ($item) use ($callback) {
                return $callback($item);
            });
        }
        return $res;
    }
}
