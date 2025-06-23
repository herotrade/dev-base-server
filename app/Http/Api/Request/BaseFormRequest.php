<?php

declare(strict_types=1);

namespace App\Http\Api\Request;

use App\Http\Common\Request\Traits\ActionRulesTrait;
use Hyperf\Validation\Request\FormRequest;

class BaseFormRequest extends FormRequest
{
    use ActionRulesTrait;

    /**
     * 是否授权访问
     */
    public function authorize(): bool
    {
        return true;
    }
}
