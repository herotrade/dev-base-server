<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq

 */

namespace App\Model\Enums\User;

use Hyperf\Constants\Annotation\Constants;
use Hyperf\Constants\Annotation\Message;
use Hyperf\Constants\EnumConstantsTrait;

#[Constants]
enum Type: int
{
    use EnumConstantsTrait;

    #[Message('user.enums.type.100')]
    case SYSTEM = 100;

    #[Message('user.enums.type.200')]
    case USER = 200;
}
