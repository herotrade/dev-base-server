<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq
 
 */

namespace App\Service\LogStash;

use App\Repository\Logstash\UserOperationLogRepository;
use App\Service\IService;

final class UserOperationLogService extends IService
{
    public function __construct(
        protected readonly UserOperationLogRepository $repository
    ) {}
}
