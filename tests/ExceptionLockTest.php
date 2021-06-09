<?php

declare(strict_types=1);

namespace HyperfTest\Synchronized;


use Hyperf\Synchronized\Annotation\Synchronized;
use Hyperf\Synchronized\LockMode;
use Hyperf\Utils\Parallel;
use PHPUnit\Framework\TestCase;

class ExceptionLockTest extends TestCase
{
    public function testLock(){

        $parallel = new Parallel(5);

        $parallel->add([$this, 'handler']);
        $parallel->add([$this, 'handler']);
        $parallel->add([$this, 'handler']);
        $parallel->add([$this, 'handler']);
        $parallel->add([$this, 'handler']);

        $e = null;
        try{
            $parallel->wait(true);
        }catch (\Throwable $exception){
            $e = $exception;
        }
        $this->assertTrue(true);
    }

    /**
     * @Synchronized(mode=LockMode::EXCEPTION)
     */
    public function handler():void{

    }
}