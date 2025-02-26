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

use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\UploadRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\CurrentUser;
use App\Schema\AttachmentSchema;
use App\Service\AttachmentService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;
use Symfony\Component\Finder\SplFileInfo;

#[Controller]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: OperationMiddleware::class, priority: 98)]
final class AttachmentController extends AbstractController
{
    public function __construct(
        protected readonly AttachmentService $service,
        protected readonly CurrentUser $currentUser
    ) {}

    #[RequestMapping(
        path: '/admin/attachment/list',
        methods: ["GET"],
    )]
    #[Permission(code: 'dataCenter:attachment:list')]
    #[PageResponse(instance: AttachmentSchema::class)]
    public function list(): Result
    {
        $params = $this->getRequest()->all();
        $params['current_user_id'] = $this->currentUser->id();
        if (isset($params['suffix'])) {
            $params['suffix'] = explode(',', $params['suffix']);
        }
        return $this->success(
            $this->service->page($params, $this->getCurrentPage(), $this->getPageSize())
        );
    }

    #[RequestMapping(
        path: '/admin/attachment/upload',
        methods: ["POST"],
    )]
    #[Permission(code: 'dataCenter:attachment:upload')]
    #[ResultResponse(instance: new Result())]
    public function upload(UploadRequest $request): Result
    {
        $uploadFile = $request->file('file');
        $newTmpPath = sys_get_temp_dir() . '/' . uniqid() . '.' . $uploadFile->getExtension();
        $uploadFile->moveTo($newTmpPath);
        $splFileInfo = new SplFileInfo($newTmpPath, '', '');
        return $this->success(
            $this->service->upload($splFileInfo, $uploadFile, $this->currentUser->id())
        );
    }

    #[RequestMapping(
        path: '/admin/attachment/{id}',
        methods: ["DELETE"],
    )]
    #[Permission(code: 'dataCenter:attachment:delete')]
    #[ResultResponse(instance: new Result())]
    public function delete(int $id): Result
    {
        if (! $this->service->getRepository()->existsById($id)) {
            return $this->error(trans('attachment.attachment_not_exist'));
        }
        $this->service->deleteById($id);
        return $this->success();
    }
}
