<?php

declare(strict_types=1);
/**
 * This file is part of AlgoQuant.
 *
 * @link     https://www.algoquant.pro
 * @document https://doc.algoquant.pro
 * @contact  @chenmaq
 
 */
use Hyperf\HttpServer\Router\Router;

Router::get('/', static function () {
    return 'welcome use dev-base-server';
});

Router::get('/favicon.ico', static function () {
    return '';
});
