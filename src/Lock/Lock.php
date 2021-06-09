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


use Hyperf\Synchronized\Contract\LockInterface;
use Hyperf\Synchronized\Contract\StoreInterface;

class Lock implements LockInterface
{

    private $store;
    private $key;


    public function __construct(StoreInterface $store, string $key)
    {
        $this->store = $store;
        $this->key   = $key;
    }


    public function acquire(bool $blocking = false): bool
    {
        if (!$this->processLockAcquire($blocking)) {
            return false;
        }

        try {
            while (!$this->store->create($this->key)) {

                if (!$blocking) {
                    return false;
                }

                usleep((100 + random_int(-10, 10)) * 1000);
            }
        } finally {
            Spinlock::release($this->key);
        }

        return true;
    }


    public function processLockAcquire(bool $blocking): bool
    {
        if (!Spinlock::acquire($blocking, $this->key)) {
            return false;
        }

        return true;
    }

    public function release(): bool
    {
        return $this->store->remove($this->key);
    }


}