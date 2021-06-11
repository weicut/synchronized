<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

return [

    'redis' => [
        'store' => \Hyperf\Synchronized\Store\RedisStore::class,
        'options' => [
            'pool' => 'default',
        ],
    ],

    'consul' => [
        'store' => \Hyperf\Synchronized\Store\ConsulStore::class,
        'options' => [
            'uri' => 'http://127.0.0.1:8500',
        ],
    ],

];
