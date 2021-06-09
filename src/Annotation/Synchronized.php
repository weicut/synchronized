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

namespace Hyperf\Synchronized\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Synchronized\LockMode;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Synchronized extends AbstractAnnotation
{

    /**
     * @var int
     */
    public $mode = LockMode::BLOCK; //锁模式，1：阻塞，2：报错， 默认为阻塞

    /**
     * @var int
     */
    public $secondsTimeout = 3; //锁的超时时间（仅阻塞模式下有效），单位：秒，默认为3秒，超过会自动解除，执行下一个

    /**
     * @var boolean
     */
    public $withParam = true; //是否携带方法参数的锁，默认 true, 若为false， 则仅基于方法


    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->mode           = (int) $this->mode;
        $this->secondsTimeout = (int) $this->secondsTimeout;
        $this->withParam      = (boolean) $this->withParam;
    }

}