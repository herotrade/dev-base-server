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

use App\Http\Admin\Request\PassportLoginRequest;
use App\Http\Admin\Vo\PassportLoginVo;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\RefreshTokenMiddleware;
use App\Http\Common\Result;
use App\Http\CurrentUser;
use App\Model\Enums\User\Type;
use App\Schema\UserSchema;
use App\Service\PassportService;
use Hyperf\Collection\Arr;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Mine\Jwt\Traits\RequestScopedTokenTrait;

#[Controller]
final class PassportController extends AbstractController
{
    use RequestScopedTokenTrait;

    public function __construct(
        private readonly PassportService $passportService,
        private readonly CurrentUser $currentUser
    ) {}

    #[RequestMapping(
        path: '/admin/passport/login',
        methods: ["POST"],
    )]
    public function login(PassportLoginRequest $request): Result
    {
        $username = (string) $request->input('username');
        $password = (string) $request->input('password');
        $browser = $request->header('User-Agent') ?: 'unknown';
        $os = $request->os();
        return $this->success(
            $this->passportService->login(
                $username,
                $password,
                Type::SYSTEM,
                $request->ip(),
                $browser,
                $os
            )
        );
    }

    #[RequestMapping(
        path: '/admin/passport/logout',
        methods: ["POST"],
    )]
    #[Middleware(AccessTokenMiddleware::class)]
    public function logout(RequestInterface $request): Result
    {
        $this->passportService->logout($this->getToken());
        return $this->success();
    }

    #[RequestMapping(
        path: '/admin/passport/getInfo',
        methods: ["GET"],
    )]
    #[Middleware(AccessTokenMiddleware::class)]
    public function getInfo(): Result
    {
        return $this->success(
            Arr::only(
                $this->currentUser->user()?->toArray() ?: [],
                ['username', 'nickname', 'avatar', 'signed', 'backend_setting', 'phone', 'email']
            )
        );
    }

    #[RequestMapping(
        path: '/admin/passport/refresh',
        methods: ["POST"],
    )]
    #[Middleware(RefreshTokenMiddleware::class)]
    public function refresh(CurrentUser $user): Result
    {
        return $this->success($user->refresh());
    }
}
