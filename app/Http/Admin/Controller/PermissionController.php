<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq

 */

namespace App\Http\Admin\Controller;

use App\Exception\BusinessException;
use App\Http\Admin\Request\Permission\PermissionRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Model\Enums\User\Status;
use App\Repository\Permission\MenuRepository;
use App\Repository\Permission\RoleRepository;
use App\Schema\MenuSchema;
use App\Schema\RoleSchema;
use App\Service\Permission\UserService;
use Hyperf\Collection\Arr;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[Controller]
#[Middleware(AccessTokenMiddleware::class)]
final class PermissionController extends AbstractController
{
    public function __construct(
        private readonly CurrentUser $currentUser,
        private readonly MenuRepository $repository,
        private readonly RoleRepository $roleRepository,
        private readonly UserService $userService
    ) {}

    #[RequestMapping(
        path: '/admin/permission/menus',
        methods: ["GET"],
    )]
    #[PageResponse(
        instance: MenuSchema::class,
        example: '{"code":200,"message":"成功","data":[{"id":290,"parent_id":0,"name":"LAme6dFrlf","code":"eNiYagCtJp","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[{"id":291,"parent_id":290,"name":"zFFsqwN3rB","code":"isz4eTJANV","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[]}]},{"id":291,"parent_id":290,"name":"zFFsqwN3rB","code":"isz4eTJANV","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[]},{"id":292,"parent_id":0,"name":"mMMSlHc8cv","code":"xzobstyEmP","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[{"id":293,"parent_id":292,"name":"8Sr5vtPSqw","code":"9SelwHGooE","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[]}]},{"id":293,"parent_id":292,"name":"8Sr5vtPSqw","code":"9SelwHGooE","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[]},{"id":294,"parent_id":0,"name":"ot8fL3u7QZ","code":"kCbrLhgYDj","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[{"id":295,"parent_id":294,"name":"6uQFNiMzJa","code":"GVvC2iPU92","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[]}]},{"id":295,"parent_id":294,"name":"6uQFNiMzJa","code":"GVvC2iPU92","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[]}]}'
    )]
    public function menus(): Result
    {
        return $this->success(
            data: $this->currentUser->isSuperAdmin()
                ? $this->repository->list([
                    'status' => Status::Normal,
                    'children' => true,
                    'parent_id' => 0,
                ])
                : $this->currentUser->filterCurrentUser()
        );
    }

    #[RequestMapping(
        path: '/admin/permission/roles',
        methods: ["GET"],
    )]
    #[PageResponse(
        instance: RoleSchema::class,
        example: '{"code":200,"message":"成功","data":[{"id":290,"parent_id":0,"name":"LAme6dFrlf","code":"eNiYagCtJp","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[{"id":291,"parent_id":290,"name":"zFFsqwN3rB","code":"isz4eTJANV","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[]}]},{"id":291,"parent_id":290,"name":"zFFsqwN3rB","code":"isz4eTJANV","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[]},{"id":292,"parent_id":0,"name":"mMMSlHc8cv","code":"xzobstyEmP","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[{"id":293,"parent_id":292,"name":"8Sr5vtPSqw","code":"9SelwHGooE","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[]}]},{"id":293,"parent_id":292,"name":"8Sr5vtPSqw","code":"9SelwHGooE","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[]},{"id":294,"parent_id":0,"name":"ot8fL3u7QZ","code":"kCbrLhgYDj","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[{"id":295,"parent_id":294,"name":"6uQFNiMzJa","code":"GVvC2iPU92","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[]}]},{"id":295,"parent_id":294,"name":"6uQFNiMzJa","code":"GVvC2iPU92","icon":"test","route":"test","component":"test","redirect":"test","is_hidden":1,"type":"M","status":1,"sort":1,"created_by":1,"updated_by":1,"created_at":"2024-08-02 00:32:26","updated_at":"2024-08-02 00:32:26","deleted_at":null,"remark":"test","children":[]}]}'
    )]
    public function roles(): Result
    {
        return $this->success(
            data: $this->currentUser->isSuperAdmin()
                ? $this->roleRepository->list(['status' => Status::Normal])
                : $this->currentUser->user()->getRoles(['name', 'code', 'remark'])
        );
    }

    #[RequestMapping(
        path: '/admin/permission/update',
        methods: ["POST"],
    )]
    #[ResultResponse(new Result())]
    public function update(PermissionRequest $request): Result
    {
        $data = $request->validated();
        $user = $this->currentUser->user();
        if (Arr::exists($data, 'new_password')) {
            if (! $user->verifyPassword(Arr::get($data, 'old_password'))) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, trans('user.old_password_error'));
            }
            $data['password'] = $data['new_password'];
        }
        $this->userService->updateById($user->id, $data);
        return $this->success();
    }
}
