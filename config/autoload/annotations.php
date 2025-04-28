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
use Hyperf\Database\Commands\Migrations\GenMigrateCommand;
use Hyperf\Database\Migrations\MigrationCreator;
use Hyperf\Database\Migrations\Migrator;

return [
    'scan' => [
        'paths' => [
            BASE_PATH . '/app'
        ],
        'collectors' => [],
        'ignore_annotations' => ['mixin'],
        'class_map' => [
            ModelRewriteGetterSetterVisitor::class => BASE_PATH . '/class_map/Hyperf/Database/Commands/Ast/ModelRewriteGetterSetterVisitor.php',
            GenMigrateCommand::class => BASE_PATH.'/class_map/GenMigrateCommand.php',
            MigrationCreator::class => BASE_PATH.'/class_map/MigrationCreator.php',
            Migrator::class => BASE_PATH.'/class_map/Migrator.php',
        ]
    ],
];
