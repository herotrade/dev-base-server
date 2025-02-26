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
use App\Http\Admin\Request\Permission\BatchGrantRolesForUserRequest;
use App\Http\Admin\Request\Permission\UserRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\CurrentUser;
use App\Model\Permission\Role;
use App\Schema\UserSchema;
use App\Service\Permission\UserService;
use Hyperf\Collection\Arr;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Mine\Access\Attribute\Permission;
use Mine\Support\Middleware\CorsMiddleware;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;
use OpenApi\Attributes\RequestBody;

#[Controller]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: OperationMiddleware::class, priority: 98)]
#[Middleware(middleware: CorsMiddleware::class, priority: 101)]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly CurrentUser $currentUser
    ) {}

    #[RequestMapping(
        path: '/admin/user/list',
        methods: ["GET"],
    )]
    #[Permission(code: 'permission:user:index')]
    public function pageList(): Result
    {
        return $this->success(
            $this->userService->page(
                $this->getRequestData(),
                $this->getCurrentPage(),
                $this->getPageSize()
            )
        );
    }

    #[RequestMapping(
        path: '/admin/user',
        methods: ["PUT"],
    )]
    #[Permission(code: 'permission:user:update')]
    #[ResultResponse(new Result())]
    public function updateInfo(UserRequest $request): Result
    {
        $this->userService->updateById($this->currentUser->id(), Arr::except($request->validated(), ['password']));
        return $this->success();
    }

    #[RequestMapping(
        path: '/admin/user/password',
        methods: ["PUT"],
    )]
    #[Permission(code: 'permission:user:password')]
    #[ResultResponse(new Result())]
    public function resetPassword(): Result
    {
        return $this->userService->resetPassword($this->getRequest()->input('id'))
            ? $this->success()
            : $this->error();
    }

    #[RequestMapping(
        path: '/admin/user',
        methods: ["POST"],
    )]
    #[Permission(code: 'permission:user:save')]
    #[ResultResponse(new Result())]
    public function create(UserRequest $request): Result
    {
        $this->userService->create(array_merge($request->validated(), [
            'created_by' => $this->currentUser->id(),
        ]));
        return $this->success();
    }

    #[RequestMapping(
        path: '/admin/user',
        methods: ["DELETE"],
    )]
    #[Permission(code: 'permission:user:delete')]
    #[ResultResponse(new Result())]
    public function delete(): Result
    {
        $this->userService->deleteById($this->getRequestData());
        return $this->success();
    }

    #[RequestMapping(
        path: '/admin/user/{userId}',
        methods: ["PUT"],
    )]
    #[Permission(code: 'permission:user:update')]
    #[ResultResponse(new Result())]
    public function save(int $userId, UserRequest $request): Result
    {
        $this->userService->updateById($userId, array_merge($request->validated(), [
            'updated_by' => $this->currentUser->id(),
        ]));
        return $this->success();
    }

    #[RequestMapping(
        path: '/admin/user/{userId}/roles',
        methods: ["GET"],
    )]
    #[Permission(code: 'permission:user:getRole')]
    #[ResultResponse(new Result())]
    public function getUserRole(int $userId): Result
    {
        return $this->success($this->userService->getUserRole($userId)->map(static fn (Role $role) => $role->only([
            'id',
            'code',
            'name',
        ])));
    }

    #[RequestMapping(
        path: '/admin/user/{userId}/roles',
        methods: ["PUT"],
    )]
    #[Permission(code: 'permission:user:setRole')]
    #[ResultResponse(new Result())]
    public function batchGrantRolesForUser(int $userId, BatchGrantRolesForUserRequest $request): Result
    {
        $this->userService->batchGrantRoleForUser($userId, $request->input('role_codes'));
        return $this->success();
    }
}
