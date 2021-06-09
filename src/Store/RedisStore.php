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


use Hyperf\Redis\RedisProxy;
use Hyperf\Synchronized\Contract\StoreInterface;
use Hyperf\Synchronized\Exception\InvalidTtlException;

class RedisStore implements StoreInterface
{

    /** @var RedisProxy */
    private $redis;

    private $ttl;


    public function __construct($redis, int $ttl = 300)
    {

        if ($ttl <= 0) {
            throw new InvalidTtlException('invalid lock ttl !');
        }
        $this->redis = $redis;
        $this->ttl   = $ttl;
    }

    public function create(string $key): bool
    {
        $status = $this->redis->set(
            $key,
            1,
            ['NX', 'EX' => $this->ttl]);

        return (bool) $status;
    }

    public function exists(string $key): bool
    {
        return (bool) $this->redis->exists($key);
    }

    public function remove(string $key): bool
    {
        $this->redis->del($key);
        return true;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }


}
