<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq

 */

namespace App\Exception\Handler;

use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use Hyperf\Validation\UnauthorizedException;

final class UnauthorizedExceptionHandler extends AbstractHandler
{
    public function handleResponse(\Throwable $throwable): Result
    {
        return new Result(
            ResultCode::FORBIDDEN,
        );
    }

    public function isValid(\Throwable $throwable): bool
    {
        return $throwable instanceof UnauthorizedException;
    }
}
