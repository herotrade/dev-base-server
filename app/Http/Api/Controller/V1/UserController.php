<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq

 */

namespace App\Http\Api\Controller\V1;

use App\Http\Api\Request\V1\UserRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Result;
use App\Service\PassportService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;

#[HyperfServer(name: 'http')]
final class UserController extends AbstractController
{
    #[Inject]
    private readonly PassportService $passportService;

    #[Post(
        path: '/api/v1/login',
        operationId: 'ApiV1Login',
        summary: '用户登录',
        tags: ['api'],
    )]
    public function login(UserRequest $request): Result
    {
        return $this->success(
            $this->passportService->login(
                $request->input('username'),
                $request->input('password')
            )
        );
    }
}
