<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq

 */

namespace App\Http\Admin\Vo;

use Hyperf\Swagger\Annotation as OA;

#[OA\Schema(
    description: '登录成功返回',
    example: '{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MjIwOTQwNTYsIm5iZiI6MTcyMjA5NDAiwiZXhwIjoxNzIyMDk0MzU2fQ.7EKiNHb_ZeLJ1NArDpmK6sdlP7NsDecsTKLSZn_3D7k","expire_at":300}'
)]
final class PassportLoginVo
{
    #[OA\Property(
        description: 'Access Token',
        type: 'string',
        example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MjIwOTQwNTYsIm5iZiI6MTcyMjA5NDAiwiZXhwIjoxNzIyMDk0MzU2fQ.7EKiNHb_ZeLJ1NArDpmK6sdlP7NsDecsTKLSZn_3D7k'
    )]
    public string $access_token;

    #[OA\Property(
        description: 'Refresh Token',
        type: 'string',
        example: 'eyJ0eXAiOi'
    )]
    public string $refresh_token;

    #[OA\Property(
        description: '过期时间,单位秒',
        type: 'integer',
        example: 300
    )]
    public int $expire_at;
}
