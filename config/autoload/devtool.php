<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq

 */
return [
    'generator' => [
        'amqp' => [
            'consumer' => [
                'namespace' => 'App\Amqp\Consumer',
            ],
            'producer' => [
                'namespace' => 'App\Amqp\Producer',
            ],
        ],
        'aspect' => [
            'namespace' => 'App\Aspect',
        ],
        'command' => [
            'namespace' => 'App\Command',
        ],
        'controller' => [
            'namespace' => 'App\Http\Controller',
        ],
        'job' => [
            'namespace' => 'App\Job',
        ],
        'listener' => [
            'namespace' => 'App\Listener',
        ],
        'middleware' => [
            'namespace' => 'App\Middleware',
        ],
        'Process' => [
            'namespace' => 'App\Processes',
        ],
    ],
];
