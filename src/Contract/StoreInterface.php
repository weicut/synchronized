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

namespace Hyperf\Synchronized\Contract;


interface StoreInterface
{

    public function create(string $key): bool;

    public function exists(string $key): bool;

    public function remove(string $key): bool;

    public function getTtl(): int;
}