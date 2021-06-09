# Synchronized

[hyperf/synchronized](https://github.com/hyperf/synchronized)  提供了基于 `redis` 实现的高效互斥锁注解，底层通过增加`自旋锁` 特性解决 `redis` 轮询带来的大量网络消耗，目前仅支持 `METHOD` 注解

## 安装

```bash
composer require hyperf/synchronized
```

## 注解参数

|  配置  |                  默认值                  |         备注          |
|:------:|:----------------------------------------:|:---------------------:|
| mode |  1  | 锁模式，1：阻塞，2：异常 |
| secondsTimeout | 3 |        超时时间，仅`阻塞`模式下有效，单位：秒         |
| lockPool |        default         |       redis 的`pool`名称       |
| withParam |        true         |       false：方法锁，true：方法 + 参数锁，由此控制锁的粒度      |

## 使用

### 阻塞锁

任何时刻只会存在一个锁，当某个调用进入临界点后，其余调用会进行 `阻塞`，直到锁解除或者超时。此模式不建议使用在请求量较大的场景中，因为阻塞行为容易引起 `惊群效应`

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Hyperf\Synchronized\Annotation\Synchronized;
use Hyperf\Synchronized\LockMode;
use Hyperf\Utils\Parallel;

class BlockService
{

    public function test(){

        $startTime = microtime(true);
        $parallel = new Parallel(5);
        $parallel->add([$this, 'handler']);
        $parallel->add([$this, 'handler']);
        $parallel->add([$this, 'handler']);
        $parallel->add([$this, 'handler']);
        $parallel->add([$this, 'handler']);
        $parallel->wait();
        $endTime = microtime(true);
        echo ($endTime- $startTime).PHP_EOL;
    }

    /**
     * @Synchronized(mode=LockMode::BLOCK, secondsTimeout=3)
     */
    public function handler(): void
    {
        sleep(1);
    }
}
```

### 异常锁

任何时刻只会存在一个锁，当某个调用进入临界点后，其余调用会抛 `Hyperf\Synchronized\Exception\AcquireException` 异常。业务自行捕获处理，重试或者直接向客户端返回特定错误码，由客户端进行定时重试，减轻服务端连接负荷。
此模式比较合适请求量大的场景

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Hyperf\Synchronized\Annotation\Synchronized;
use Hyperf\Synchronized\Exception\AcquireException;
use Hyperf\Synchronized\LockMode;
use Hyperf\Utils\Coroutine;

class ExceptionService
{

    public function test(){

        for($i = 0; $i < 10; $i ++){

            Coroutine::create(function(){
                try {
                    $this->handler();
                }catch (AcquireException $exception){
                    
                }
            });
        }
    }

    /**
     * @Synchronized(mode=LockMode::EXCEPTION, secondsTimeout=3)
     */
    public function handler(): void
    {
        sleep(1);
    }
}
```