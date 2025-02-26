<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq
 
 */

namespace App\Http\Admin\Request\Permission;

use App\Schema\UserSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(
    schema: UserSchema::class,
    only: [
        'nickname', 'password', 'avatar', 'signed', 'backend_setting',
    ]
)]
class PermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nickname' => 'sometimes|string|max:255',
            'new_password' => 'sometimes|confirmed|string|min:8',
            'new_password_confirmation' => 'sometimes|string|min:8',
            'old_password' => ['sometimes', 'string'],
            'avatar' => 'sometimes|string|max:255',
            'signed' => 'sometimes|string|max:255',
            'backend_setting' => 'sometimes|array',
        ];
    }

    public function attributes(): array
    {
        return [
            'nickname' => trans('user.nickname'),
            'new_password' => trans('user.password'),
            'new_password_confirmation' => trans('user.password_confirmation'),
            'old_password' => trans('user.old_password'),
            'avatar' => trans('user.avatar'),
            'signed' => trans('user.signed'),
            'backend_setting' => trans('user.backend_setting'),
        ];
    }
}
