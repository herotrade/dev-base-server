<?php

declare(strict_types=1);

/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq
 */

use Hyperf\Database\Commands\Ast\ModelRewriteGetterSetterVisitor;

return [
    'scan' => [
        'paths' => [
            BASE_PATH . '/app'
        ],
        'collectors' => [],
        'ignore_annotations' => ['mixin'],
        'class_map' => [
            ModelRewriteGetterSetterVisitor::class => BASE_PATH . '/class_map/ModelRewriteGetterSetter.php'
        ]
    ],
];
