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


use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Redis\RedisFactory;
use Hyperf\Synchronized\Annotation\Synchronized;
use Hyperf\Synchronized\Contract\StoreInterface;
use Hyperf\Synchronized\Exception\AcquireException;
use Hyperf\Synchronized\Lock\LockFactory;
use Hyperf\Synchronized\LockKey;
use Hyperf\Synchronized\LockMode;
use Hyperf\Synchronized\Store\RedisStore;
use Hyperf\Utils\ApplicationContext;

/**
 * @Aspect
 */
class SynchronizedAspect extends AbstractAspect
{

    public $annotations = [
        Synchronized::class,
    ];

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {

        /** @var Synchronized $annotation */
        $annotation = $proceedingJoinPoint->getAnnotationMetadata()->method[Synchronized::class];

        $lockKey = $this->generateKey($proceedingJoinPoint, $annotation);

        $config = config('synchronized.'.$annotation->store);

        if(empty($config) || empty($config['store'])){
            throw new \InvalidArgumentException('invalid synchronized config.');
        }

        /** @var StoreInterface $store */
        $store = make($config['store'], ['options' => (array)($config['options'] ?? []), 'ttl' => $annotation->secondsTimeout]);

        if(!$store instanceof StoreInterface){
            throw new \InvalidArgumentException(sprintf('invalid synchronized store instance: %s', $config['store']));
        }

        $lock = LockFactory::createFromStoreAndKey($store, $lockKey);


        if (!$lock->acquire($annotation->mode === LockMode::BLOCK)) {
            throw new AcquireException('acquire lock failed.');
        }

        $this->logger->debug('acquire lock: '.$lockKey);

        try {
            return $proceedingJoinPoint->process();
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $lock->release();
            $this->logger->debug('release lock: '.$lockKey);
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