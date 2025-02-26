<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq

 */
use Mine\Casbin\Adapters\DatabaseAdapter;

return [
    /*
    * Casbin model setting.
    */
    'model' => [
        // Available Settings: "file", "text"
        'type' => 'file',

        'path' => __DIR__ . '/casbin/rbac-model.conf',

        'text' => '',
    ],

    /*
    * Casbin adapter .
    */
    //    'adapter' => DatabaseAdapter::class,

    /*
    * Database setting.
    */
    'database' => [
        // Database connection for following tables.
        'connection' => 'default',

        // Rule table name.
        'table' => 'rules',
    ],

    'log' => [
        // changes whether Lauthz will log messages to the Logger.
        'enabled' => false,
    ],
];
