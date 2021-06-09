<?php

declare(strict_types=1);

namespace HyperfTest\Synchronized;


use Hyperf\Cache\AnnotationManager;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Synchronized\Annotation\Synchronized;
use Hyperf\Synchronized\LockMode;
use Mockery;
use PHPUnit\Framework\TestCase;

class AnnotationTest extends TestCase
{
    public function testSynchronized()
    {
        $annotation = new Synchronized([
            'mode' => LockMode::BLOCK,
            'secondsTimeout' => 3,
            'withParam' => true,
            'lockPool' => 'default',
        ]);

        $this->assertSame(LockMode::BLOCK, $annotation->mode);
        $this->assertSame(3, $annotation->secondsTimeout);
        $this->assertTrue($annotation->withParam);
        $this->assertSame('default', $annotation->lockPool);

        $annotation = new Synchronized([
            'mode' => '2',
            'secondsTimeout' => '3',
            'withParam' => false,
            'lockPool' => 'default1',
        ]);

        $this->assertSame(LockMode::EXCEPTION, $annotation->mode);
        $this->assertSame(3, $annotation->secondsTimeout);
        $this->assertFalse($annotation->withParam);
        $this->assertSame('default1', $annotation->lockPool);
    }
}