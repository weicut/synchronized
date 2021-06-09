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

namespace Hyperf\Synchronized\Lock;


class ProcessLock
{

    public static $store = [];

    public static function acquire(bool $blocking, string $key): bool
    {

        while (isset(self::$store[$key])) {

            if (!$blocking) {
                return false;
            }
            usleep((100 + random_int(-10, 10)) * 1000);
        }

        self::$store[$key] = true;

        return true;
    }

    public static function release(string $key): bool
    {
        unset(self::$store[$key]);

        return true;
    }


}