<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq
 */

namespace App\Http\Api\Controller\User;

use App\Http\Api\Middleware\TokenMiddleware;
use App\Http\Api\Request\User\LoginRequest;
use App\Http\Api\Request\User\RegisterRequest;
use App\Http\Api\Service\User\UserService;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Middleware\RefreshTokenMiddleware;
use App\Http\Common\Result;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Mine\Jwt\Traits\RequestScopedTokenTrait;

#[Controller(prefix: 'api/user')]
final class UserController extends AbstractController
{
    use RequestScopedTokenTrait;

    #[Inject]
    private readonly UserService $userService;

    /**
     * 用户登录
     */
    #[PostMapping('login')]
    public function login(LoginRequest $request): Result
    {
        $ip = $request->getServerParams()['remote_addr'] ?? '0.0.0.0';
        $userAgent = $request->getHeaderLine('User-Agent');

        // 简单解析 User-Agent 获取浏览器和操作系统信息
        $browser = 'unknown';
        $os = 'unknown';

        if (preg_match('/MSIE|Edge|Chrome|Safari|Firefox|Opera/i', $userAgent, $matches)) {
            $browser = $matches[0];
        }

        if (preg_match('/Windows|Linux|Mac OS X|Android|iOS/i', $userAgent, $matches)) {
            $os = $matches[0];
        }

        return $this->success(
            $this->userService->login(
                $request->input('username'),
                $request->input('password'),
                $ip,
                $browser,
                $os
            )
        );
    }

    /**
     * 用户注册
     */
    #[PostMapping('register')]
    public function register(RegisterRequest $request): Result
    {
        return $this->success(
            $this->userService->register($request->all())
        );
    }

    /**
     * 用户退出登录
     */
    #[DeleteMapping('logout')]
    #[Middleware(middleware: TokenMiddleware::class, priority: 100)]
    public function logout(RequestInterface $request): Result
    {
        $this->userService->logout($this->getToken());
        return $this->success();
    }

    /**
     * 刷新令牌
     */
    #[GetMapping('refresh/token')]
    #[Middleware(middleware: RefreshTokenMiddleware::class, priority: 100)]
    public function refresh(RequestInterface $request): Result
    {
        return $this->success(
            $this->userService->refreshToken($this->getToken())
        );
    }
}
