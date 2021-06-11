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

namespace Hyperf\Synchronized\Store;


use Hyperf\Synchronized\Contract\StoreInterface;

class RedisRedLockStore implements StoreInterface
{

    protected $handler;

    protected $ttl;


    public function __construct(array $options, int $ttl)
    {
        $this->ttl     = $ttl;
    }

    public function create(string $key): bool
    {
        return true;
    }

    public function remove(string $key): bool
    {
        return true;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }


}
