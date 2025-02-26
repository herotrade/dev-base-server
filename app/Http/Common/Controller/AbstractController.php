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

use App\Http\Common\Result;
use App\Http\Common\ResultCode;

abstract class AbstractController
{
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
