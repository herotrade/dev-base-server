<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq
 */

namespace App\Http\Api\Service\User;

use App\Exception\BusinessException;
use App\Exception\JwtInBlackException;
use App\Http\Common\ResultCode;
use App\Model\Enums\User\Type;
use App\Model\Permission\User;
use App\Repository\Permission\UserRepository;
use Hyperf\Di\Annotation\Inject;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\UnencryptedToken;
use Mine\Jwt\Factory;
use Mine\Jwt\JwtInterface;
use Mine\JwtAuth\Event\UserLoginEvent;
use Mine\JwtAuth\Interfaces\CheckTokenInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final class UserService implements CheckTokenInterface
{
    /**
     * @var string jwt场景
     */
    private string $jwt = 'api';

    #[Inject]
    private readonly UserRepository $userRepository;

    #[Inject]
    private readonly Factory $jwtFactory;

    #[Inject]
    private readonly EventDispatcherInterface $dispatcher;

    /**
     * 用户登录
     */
    public function login(string $username, string $password, string $ip = '0.0.0.0', string $browser = 'unknown', string $os = 'unknown'): array
    {
        $user = $this->userRepository->findByUnameType($username, Type::USER);
        if (!$user->verifyPassword($password)) {
            $this->dispatcher->dispatch(new UserLoginEvent($user, $ip, $os, $browser, false));
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, trans('auth.password_error'));
        }

        $this->dispatcher->dispatch(new UserLoginEvent($user, $ip, $os, $browser));
        $jwt = $this->getJwt();

        return [
            'access_token' => $jwt->builderAccessToken((string) $user->id)->toString(),
            'refresh_token' => $jwt->builderRefreshToken((string) $user->id)->toString(),
            'expire_at' => (int) $jwt->getConfig('ttl', 0),
        ];
    }

    /**
     * 用户注册
     */
    public function register(array $data): array
    {
        // 创建用户
        $user = new User();
        $user->username = $data['username'];
        $user->password = $data['password']; // 模型中已加密
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? '';
        $user->nickname = $data['nickname'];
        $user->user_type = Type::USER;
        $user->status = 1;

        if (!$user->save()) {
            throw new BusinessException(ResultCode::FAIL, trans('user.register_failed'));
        }

        // 注册成功后自动登录
        return $this->login($data['username'], $data['password']);
    }

    /**
     * 用户退出登录
     */
    public function logout(UnencryptedToken $token): bool
    {
        $this->getJwt()->addBlackList($token);
        return true;
    }

    /**
     * 刷新令牌
     */
    public function refreshToken(UnencryptedToken $token): array
    {
        $jwt = $this->getJwt();
        $jwt->addBlackList($token);

        return [
            'access_token' => $jwt->builderAccessToken($token->claims()->get(RegisteredClaims::ID))->toString(),
            'refresh_token' => $jwt->builderRefreshToken($token->claims()->get(RegisteredClaims::ID))->toString(),
            'expire_at' => (int) $jwt->getConfig('ttl', 0),
        ];
    }

    /**
     * 检查JWT令牌
     */
    public function checkJwt(UnencryptedToken $token): void
    {
        $this->getJwt()->hasBlackList($token) && throw new JwtInBlackException();
    }

    /**
     * 获取JWT实例
     */
    public function getJwt(): JwtInterface
    {
        return $this->jwtFactory->get($this->jwt);
    }
}
