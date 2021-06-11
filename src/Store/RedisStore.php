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


use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\RedisProxy;
use Hyperf\Synchronized\Contract\StoreInterface;
use Hyperf\Utils\ApplicationContext;

class RedisStore implements StoreInterface
{

    protected $handler;

    protected $ttl;


    public function __construct(array $options, int $ttl)
    {
        $this->handler = $this->makeClient($options);
        $this->ttl     = $ttl;
    }

    private function makeClient(array $options):RedisProxy
    {
        $pool = $options['pool'] ?? 'default';
        return ApplicationContext::getContainer()->get(RedisFactory::class)->get($pool);
    }

    public function create(string $key): bool
    {
        $status = $this->handler->set(
            $key,
            1,
            ['NX', 'EX' => $this->ttl]);

        return (bool) $status;
    }

    public function remove(string $key): bool
    {
        $this->handler->del($key);
        return true;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }


}
