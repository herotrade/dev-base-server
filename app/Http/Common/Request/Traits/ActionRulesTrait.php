<?php

declare(strict_types=1);

namespace App\Http\Common\Request\Traits;


use Hyperf\Validation\ValidationException;

trait ActionRulesTrait
{
    /**
     * 获取当前请求方法
     *
     * @return string
     */
    abstract public function getMethod(): string;

    /**
     * 自动根据请求方法选择对应的 rules 方法
     *
     * @return array
     * @throws ValidationException
     */
    public function rules(): array
    {
        $method = strtolower($this->getMethod());

        $actionMap = [
            'post' => 'createRules',
            'put' => 'updateRules',
            'patch' => 'updateRules',
            'get' => 'getRules',
            'delete' => 'deleteRules',
        ];

        if (isset($actionMap[$method])) {
            $ruleMethod = $actionMap[$method];
            if (method_exists($this, $ruleMethod)) {
                return $this->{$ruleMethod}();
            }
        }

        // 如果没有对应方法，尝试调用通用 rules()
        if (method_exists($this, 'defaultRules')) {
            return $this->defaultRules();
        }

        // 如果没有任何规则定义，则返回空数组
        return [];
    }

    /**
     * 默认规则（可选）
     *
     * @return array
     */
    public function defaultRules(): array
    {
        return [];
    }

    /**
     * 自动获取 attribute 映射（字段中文名）
     *
     * @return array
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * 自定义错误消息（可选）
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }
}