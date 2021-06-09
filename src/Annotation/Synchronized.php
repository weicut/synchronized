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
    public $mode = LockMode::BLOCK;

    /**
     * @var int
     */
    public $secondsTimeout = 3;

    /**
     * @var boolean
     */
    public $withParam = true;

    /**
     * @var string
     */
    public $mutexPool = 'default';


    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->mode           = (int) $this->mode;
        $this->secondsTimeout = (int) $this->secondsTimeout;
        $this->withParam      = (boolean) $this->withParam;
        $this->mutexPool      = (string) $this->mutexPool;
    }

}