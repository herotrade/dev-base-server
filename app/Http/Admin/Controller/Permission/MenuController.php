<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq

 */

namespace App\Http\Admin\Controller\Permission;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Permission\MenuRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\CurrentUser;
use App\Service\Permission\MenuService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Mine\Access\Attribute\Permission;
use Mine\Support\Middleware\CorsMiddleware;

#[Controller]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: OperationMiddleware::class, priority: 98)]
final class MenuController extends AbstractController
{
    public function __construct(
        private readonly MenuService $service,
        private readonly CurrentUser $user
    ) {}

    #[RequestMapping(
        path: '/admin/menu/list',
        methods: ["GET"],
    )]
    #[Permission(code: 'permission:menu:index')]
    public function pageList(RequestInterface $request): Result
    {
        return $this->success(data: $this->service->getRepository()->list([
            'children' => true,
            'parent_id' => 0,
        ]));
    }

    #[RequestMapping(
        path: '/admin/menu',
        methods: ["POST"],
    )]
    #[Permission(code: 'permission:menu:create')]
    public function create(MenuRequest $request): Result
    {
        $this->service->create(array_merge($request->validated(), [
            'created_by' => $this->user->id(),
        ]));
        return $this->success();
    }

    #[RequestMapping(
        path: '/admin/menu/{id}',
        methods: ["PUT"],
    )]
    #[Permission(code: 'permission:menu:save')]
    public function save(int $id, MenuRequest $request): Result
    {
        $this->service->updateById($id, array_merge($request->validated(), [
            'updated_by' => $this->user->id(),
        ]));
        return $this->success();
    }

    #[RequestMapping(
        path: '/admin/menu',
        methods: ["DELETE"],
    )]
    #[Permission(code: 'permission:menu:delete')]
    public function delete(): Result
    {
        $this->service->deleteById($this->getRequestData());
        return $this->success();
    }
}
