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

namespace Hyperf\Synchronized\Aspect;


use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Redis\RedisFactory;
use Hyperf\Synchronized\Annotation\Synchronized;
use Hyperf\Synchronized\Exception\AcquireException;
use Hyperf\Synchronized\Lock\LockFactory;
use Hyperf\Synchronized\LockKey;
use Hyperf\Synchronized\LockMode;
use Hyperf\Synchronized\Store\RedisStore;
use Hyperf\Utils\ApplicationContext;
use Throwable;

/**
 * @Aspect
 */
class SynchronizedAspect extends AbstractAspect
{

    public $annotations = [
        Synchronized::class,
    ];


    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {

        /** @var Synchronized $synchronized */
        $synchronized = $proceedingJoinPoint->getAnnotationMetadata()->method[Synchronized::class];

        $redis   = ApplicationContext::getContainer()->get(RedisFactory::class)->get('default');
        $lockKey = $this->generateKey($proceedingJoinPoint, $synchronized);

        $store = new RedisStore($redis, (int) $synchronized->secondsTimeout);

        $lock = LockFactory::createFromStoreAndKey($store, $lockKey);


        if (!$lock->acquire($synchronized->mode == LockMode::BLOCK)) {
            throw new AcquireException('acquire lock failed !');
        }


        try {
            return $proceedingJoinPoint->process();
        } catch (Throwable $throwable) {
            throw $throwable;
        } finally {
            $lock->release();
        }
    }


    public function generateKey(ProceedingJoinPoint $proceedingJoinPoint, Synchronized $synchronized): string
    {

        $reflectionMethod = $proceedingJoinPoint->getReflectMethod();
        $method           = sprintf('%s:%s:%s', LockKey::PREFIX, $reflectionMethod->class, $reflectionMethod->name);


        if ($synchronized->withParam) {
            $method = sprintf('%s:%s', $method,
                md5((string) json_encode($proceedingJoinPoint->getArguments(), JSON_PARTIAL_OUTPUT_ON_ERROR)));
        }

        return $method;
    }
}