<?php

declare(strict_types=1);
/**
 * 策略平台API
 * 币种请求验证类
 */

namespace App\Http\Admin\Request\Currency;

use Hyperf\Validation\Request\FormRequest;

class CurrencyRequest extends FormRequest
{
    public function authorize()
    {
        // 如果有复杂的权限控制（如：更细粒度的检查用户是否有权操作特定资源的权限控制），可以在这里实现
        // 如果单纯依赖 Permission 注解对按钮权限(对应到接口)控制且已经在 Controller 中通过注解实现，则直接返回 true
        return true;
    }
    /**
     * 验证规则
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|array',
            'symbol' => 'required|string|max:20|unique:currency,symbol',
            'icon' => 'nullable|string|max:255',
            'sort' => 'required|numeric|min:0',
            'decimals' => 'required|integer|min:0|max:18',
        ];

        // 更新操作时排除当前记录
        if ($this->isMethod('PUT')) {
            $id = $this->route('id');
            $rules['symbol'] = "required|string|max:20|unique:currency,symbol,{$id}";
        }

        return $rules;
    }

    /**
     * 错误消息
     */
    public function messages(): array
    {
        return [
            'name' => '币种名称不能为空',
            'symbol' => '币种代码不能为空',
            'sort' => '排序不能为空',
            'decimals' => '精度不能为空'
        ];
    }
}
