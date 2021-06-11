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

namespace Hyperf\Synchronized;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'annotations'  => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for synchronized.',
                    'source' => __DIR__ . '/../publish/synchronized.php',
                    'destination' => BASE_PATH . '/config/autoload/synchronized.php',
                ],
            ],
        ];
    }
}
