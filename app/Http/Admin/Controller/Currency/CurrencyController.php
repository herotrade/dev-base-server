<?php

declare(strict_types=1);
/**
 * 策略平台API
 * 币种管理控制器
 */

namespace App\Http\Admin\Controller\Currency;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Request\Currency\CurrencyRequest;
use App\Http\Admin\Service\Currency\CurrencyService;
use App\Http\Common\Resource\BaseResource;
use App\Http\Common\Result;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use Mine\Access\Attribute\Permission;

#[Controller(prefix: 'admin/currency')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: OperationMiddleware::class, priority: 98)]
class CurrencyController extends AbstractController
{
    protected RequestInterface $request;

    public function __construct(
        private readonly CurrencyService $currencyService,
        RequestInterface $request
    ) {
        $this->request = $request;
        parent::__construct();
    }

    /**
     * 获取币种列表
     */
    #[GetMapping('list')]
    #[Permission(code: 'currency:index')]
    public function index(): Result
    {
        $result = $this->currencyService->list($this->request);
        return $this->success(BaseResource::collection($result));
    }

    /**
     * 获取币种详情
     */
    #[GetMapping('{id}')]
    #[Permission(code: 'currency:index')]
    public function show(int $id): Result
    {
        $currency = $this->currencyService->show($id);
        return $this->success($currency);
    }

    /**
     * 创建币种
     */
    #[PostMapping('')]
    #[Permission(code: 'currency:create')]
    public function store(CurrencyRequest $request): Result
    {
        $currency = $this->currencyService->create($request->validated());
        return $this->success($currency);
    }

    /**
     * 更新币种
     */
    #[PutMapping('{id}')]
    #[Permission(code: 'currency:update')]
    public function update(int $id, CurrencyRequest $request): Result
    {
        $currency = $this->currencyService->update($id, $request->validated());
        return $this->success($currency);
    }

    /**
     * 删除币种
     */
    #[DeleteMapping('{id}')]
    #[Permission(code: 'currency:delete')]
    public function destroy(int $id): Result
    {
        $this->currencyService->delete($id);
        return $this->success();
    }

    /**
     * 批量删除币种
     */
    #[DeleteMapping('batch/delete')]
    #[Permission(code: 'currency:delete')]
    public function batchDestroy(): Result
    {
        $ids = $this->request->input('ids', []);
        $del_count = $this->currencyService->batchDelete($ids);
        return $this->success([
            'deleted_count' => $del_count,
        ]);
    }
}
