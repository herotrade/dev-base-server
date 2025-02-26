<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq

 */
use Symfony\Component\Finder\Finder;

return [
    'enable' => false,
    'port' => 9503,
    'json_dir' => BASE_PATH . '/storage/swagger',
    'html' => BASE_PATH . '/storage/swagger/index.html',
    'url' => '/swagger',
    'auto_generate' => true,
    'scan' => [
        'paths' => Finder::create()
            ->in([BASE_PATH . '/app/Http', BASE_PATH . '/app/Schema'])
            ->name('*.php')
            ->getIterator(),
    ],
    'processors' => [],
];
