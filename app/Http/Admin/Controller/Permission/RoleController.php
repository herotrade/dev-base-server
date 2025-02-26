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

use App\Exception\BusinessException;
use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Permission\BatchGrantPermissionsForRoleRequest;
use App\Http\Admin\Request\Permission\RoleRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Model\Permission\Menu;
use App\Schema\RoleSchema;
use App\Service\Permission\RoleService;
use Hyperf\Collection\Arr;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[Controller]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: OperationMiddleware::class, priority: 98)]
final class RoleController extends AbstractController
{
    public function __construct(
        private readonly RoleService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[RequestMapping(
        path: '/admin/role/list',
        methods: ["GET"],
    )]
    #[Permission(code: 'permission:role:index')]
    public function pageList(): Result
    {
        return $this->success(
            $this->service->page(
                $this->getRequestData(),
                $this->getCurrentPage(),
                $this->getPageSize()
            )
        );
    }

    #[RequestMapping(
        path: '/admin/role',
        methods: ["POST"],
    )]
    #[Permission(code: 'permission:role:save')]
    public function create(RoleRequest $request): Result
    {
        $this->service->create(array_merge($request->validated(), [
            'created_by' => $this->currentUser->id(),
        ]));
        return $this->success();
    }

    #[RequestMapping(
        path: '/admin/role/{id}',
        methods: ["PUT"],
    )]
    #[Permission(code: 'permission:role:update')]
    public function save(int $id, RoleRequest $request): Result
    {
        $this->service->updateById($id, array_merge($request->validated(), [
            'updated_by' => $this->currentUser->id(),
        ]));
        return $this->success();
    }

    #[RequestMapping(
        path: '/admin/role',
        methods: ["DELETE"],
    )]
    #[Permission(code: 'permission:role:delete')]
    public function delete(): Result
    {
        $this->service->deleteById($this->getRequestData());
        return $this->success();
    }

    #[RequestMapping(
        path: '/admin/role/{id}/permissions',
        methods: ["GET"],
    )]
    #[Permission(code: 'permission:role:getMenu')]
    public function getRolePermissionForRole(int $id): Result
    {
        return $this->success($this->service->getRolePermission($id)->map(static fn (Menu $menu) => $menu->only([
            'id', 'name',
        ]))->toArray());
    }

    #[RequestMapping(
        path: '/admin/role/{id}/permissions',
        methods: ["PUT"],
    )]
    #[Permission(code: 'permission:role:setMenu')]
    public function batchGrantPermissionsForRole(int $id, BatchGrantPermissionsForRoleRequest $request): Result
    {
        if (! $this->service->existsById($id)) {
            throw new BusinessException(code: ResultCode::NOT_FOUND);
        }
        $permissionsCode = Arr::get($request->validated(), 'permissions', []);
        $this->service->batchGrantPermissionsForRole($id, $permissionsCode);
        return $this->success();
    }
}
