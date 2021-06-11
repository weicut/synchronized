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

namespace Hyperf\Synchronized\Store;


use Hyperf\Consul\KV;
use Hyperf\Consul\Session;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Synchronized\Contract\StoreInterface;
use Hyperf\Utils\ApplicationContext;

class ConsulStore implements StoreInterface
{

    private $ttl;

    /** @var Session */
    protected $session;
    /** @var KV */
    protected $kv;

    protected $sessionId;

    protected $retry;

    protected $logger;


    public function __construct(array $options, int $ttl, StdoutLoggerInterface $logger)
    {
        if ($ttl <= 0) {
            throw new \InvalidArgumentException('invalid parameter of ttl.');
        }
        $this->ttl = $ttl < 10 ? 10 : $ttl;
        [$this->session, $this->kv] = $this->makeClient($options);

        $this->retry = $options['retry'] ?? 3;

        $this->logger = $logger;
    }


    private function makeClient(array $options): array
    {
        $container     = ApplicationContext::getContainer();
        $clientFactory = $container->get(ClientFactory::class);

        $consulServer = $options['uri'] ?? 'http://127.0.0.1:8500';
        $token        = $options['token'] ?? '';

        $callback = static function () use ($clientFactory, $consulServer, $token) {
            $params = [
                'base_uri' => $consulServer,
            ];
            if (!empty($token)) {
                $params['headers'] = [
                    'X-Consul-Token' => $token
                ];
            }
            return $clientFactory->create($params);
        };

        return [new Session($callback), new KV($callback)];
    }

    protected function getSessionId(string $key): string
    {
        $retry = $this->getRetry();

        $sessionId = '';

        while ($retry--) {
            try {
                $response = $this->session->create([
                    'name'     => $key,
                    "Behavior" => "release",
                    "TTL"      => "10s",
                ]);

                $sessionId = (string) $response->json('ID');

            } catch (\Throwable $exception) {

                if ($retry <= 0) {
                    throw $exception;
                } else {
                    $this->logger->debug(sprintf('retry call consul session create, counter: %d',
                        $this->getRetry() - $retry));
                }
            }
        }

        return $sessionId;
    }

    public function create(string $key): bool
    {

        $retry = $this->getRetry();

        $state = true;

        while ($retry--) {
            try {

                $this->sessionId = $this->getSessionId($key);

                $response = $this->kv->put($key, 1, ['acquire' => $this->sessionId]);

                $state = (bool) $response->json();

                if (false === $state) {
                    $this->session->destroy($this->sessionId);
                }

                return $state;

            } catch (\Throwable $exception) {

                if ($retry <= 0) {
                    throw $exception;
                } else {
                    $this->logger->debug(sprintf('retry call consul kv get, counter: %d', $this->getRetry() - $retry));
                }
            }
        }

        return $state;
    }

    public function remove(string $key): bool
    {

        $retry = $this->getRetry();

        while ($retry--) {
            try {

                $this->kv->delete($key);

                if (!empty($this->sessionId)) {
                    $this->session->destroy($this->sessionId);
                }

            } catch (\Throwable $exception) {

                if ($retry <= 0) {
                    throw $exception;
                } else {
                    $this->logger->debug(sprintf('retry call consul kv delete, counter: %d',
                        $this->getRetry() - $retry));
                }
            }
        }

        return true;
    }

    protected function getRetry(): int
    {
        return $this->retry + 1;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }


}
