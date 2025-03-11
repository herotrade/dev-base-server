<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq
 */

namespace App\Http\Common\Controller;

use App\Exception\BusinessException;
use App\Exception\NormalStatusException;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Aop\PropertyHandlerTrait;
use Hyperf\Di\Aop\ProxyTrait;

class AbstractController
{
    use ProxyTrait;
    use PropertyHandlerTrait;

    private readonly mixed $service;

    /**
     * service 层自动加载规约
     */
    public function __construct()
    {
        $this->__handlePropertyHandler(__CLASS__);
        $serviceClass = '\\' . str_replace('Controller', 'Service', static::class);
        if (class_exists($serviceClass)) {
            if (!ApplicationContext::getContainer()->has($serviceClass)) {
                ApplicationContext::getContainer()->set($serviceClass, new $serviceClass());
            }
            $this->service = ApplicationContext::getContainer()->get($serviceClass);
        } else {
            throw new BusinessException(ResultCode::FAIL, "Not found service : {$serviceClass}");
        }
    }

    protected function success(mixed $data = [], ?string $message = null): Result
    {
        return new Result(ResultCode::SUCCESS, $message, $data);
    }

    protected function error(?string $message = null, mixed $data = []): Result
    {
        return new Result(ResultCode::FAIL, $message, $data);
    }

    protected function json(ResultCode $code, mixed $data = [], ?string $message = null): Result
    {
        return new Result($code, $message, $data);
    }
}