<?php

declare(strict_types=1);

namespace HyperfTest\Synchronized;


use Hyperf\Synchronized\Annotation\Synchronized;
use Hyperf\Synchronized\LockMode;
use Hyperf\Utils\Parallel;
use PHPUnit\Framework\TestCase;

class BlockLockTest extends TestCase
{

    public function testLock(){

        $startTime = microtime(true);

        $parallel = new Parallel(5);

        $parallel->add([$this, 'handler']);
        $parallel->add([$this, 'handler']);
        $parallel->add([$this, 'handler']);
        $parallel->add([$this, 'handler']);
        $parallel->add([$this, 'handler']);

        $parallel->wait();

        $endTime = microtime(true);

        self::assertGreaterThan($endTime - $startTime, 5);
    }

    /**
     * @Synchronized(mode=LockMode::BLOCK, secondsTimeout=3)
     */
    public function handler():void{
        sleep(1);
    }
}