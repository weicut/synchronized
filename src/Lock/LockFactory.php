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


use Hyperf\Synchronized\Store\StoreInterface;

class LockFactory
{

    public static function createFromStoreAndKey(StoreInterface $store, string $key): Lock
    {

        return new Lock($store, $key);
    }
}