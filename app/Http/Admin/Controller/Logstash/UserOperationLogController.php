<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq

 */

namespace App\Http\Admin\Controller\Logstash;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Result;
use App\Http\CurrentUser;
use App\Schema\UserOperationLogSchema;
use App\Service\LogStash\UserOperationLogService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;

#[Controller]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
final class UserOperationLogController extends AbstractController
{
    public function __construct(
        protected readonly UserOperationLogService $service,
        protected readonly CurrentUser $currentUser
    ) {}

    #[RequestMapping(
        path: '/admin/user-operation-log/list',
        methods: ["GET"],
    )]
    #[Permission(code: 'log:userOperation:list')]
    #[PageResponse(instance: UserOperationLogSchema::class)]
    public function page(): Result
    {
        return $this->success($this->service->page(
            $this->getRequestData(),
            $this->getCurrentPage(),
            $this->getPageSize()
        ));
    }

    #[RequestMapping(
        path: '/admin/user-operation-log',
        methods: ["DELETE"],
    )]
    #[Permission(code: 'log:userOperation:delete')]
    #[ResultResponse(instance: Result::class)]
    public function delete(RequestInterface $request): Result
    {
        $this->service->deleteById($request->input('ids'));
        return $this->success();
    }
}
