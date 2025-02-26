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

use App\Repository\Logstash\UserLoginLogRepository;
use App\Service\IService;

final class UserLoginLogService extends IService
{
    public function __construct(
        protected readonly UserLoginLogRepository $repository
    ) {}
}
