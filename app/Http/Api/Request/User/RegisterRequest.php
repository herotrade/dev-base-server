<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq
 */

namespace App\Http\Api\Request\User;

use Hyperf\Validation\Request\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:16|unique:user,username',
            'nickname' => 'required|string|max:32',
            'password' => 'required|string|min:6|max:32',
            'confirm_password' => 'required|same:password',
            'email' => 'required|email|max:64|unique:user,email',
            'phone' => 'nullable|string|max:11|unique:user,phone',
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => trans('user.username'),
            'nickname' => trans('user.nickname'),
            'password' => trans('user.password'),
            'confirm_password' => trans('user.confirm_password'),
            'email' => trans('user.email'),
            'phone' => trans('user.phone'),
        ];
    }
}
