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


use Hyperf\Synchronized\Exception\InvalidTtlException;
use Redis;

class RedisStore implements StoreInterface
{

    /** @var Redis */
    private $redis;
    private $ttl;

    /**
     * RedisStore constructor.
     * @param $redis
     * @param  float  $ttl
     */
    public function __construct($redis, float $ttl = 300.0)
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
            ['NX', 'EX' => (int) $this->ttl]);

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
