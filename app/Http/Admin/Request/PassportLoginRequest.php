<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq
 
 */

namespace App\Http\Admin\Request;

use Hyperf\Collection\Arr;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;
use Hyperf\Validation\Request\FormRequest;
use Mine\Support\Request\ClientIpRequestTrait;
use Mine\Support\Request\ClientOsTrait;

#[Schema(title: '登录请求', description: '登录请求参数', properties: [
    new Property('username', description: '用户名', type: 'string'),
    new Property('password', description: '密码', type: 'string'),
])]
class PassportLoginRequest extends FormRequest
{
    use ClientIpRequestTrait;
    use ClientOsTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|exists:user,username',
            'password' => 'required|string',
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => trans('user.username'),
            'password' => trans('user.password'),
        ];
    }

    public function ip(): string
    {
        return Arr::first($this->getClientIps(), static fn ($ip) => $ip, '0.0.0.0');
    }
}
