<?php
/**
 * This file is part of Platform package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Karma\System\Gitter;

use Gitter\Client;
use Karma\Platform\Io\AbstractSystem;
use Karma\Platform\Io\ChannelInterface;
use Karma\Platform\Io\SystemInterface;
use Karma\Platform\Io\UserInterface;
use Karma\Platform\Support\IdentityMap;
use Karma\Platform\Support\Loggable;
use Karma\Platform\Support\LoggableInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;

/**
 * Class GitterSystem
 * @package Karma\System\Gitter
 */
class GitterSystem extends AbstractSystem
{
    /**
     * System name
     */
    private const SYSTEM_NAME = 'gitter';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var UserInterface|null
     */
    private $auth;

    /**
     * GitterSystem constructor.
     * @param string $token
     * @throws \DomainException
     */
    public function __construct(string $token)
    {
        if (!class_exists(Client::class)) {
            throw new \DomainException('"serafim/gitter-api": "~4.0" required');
        }

        $this->client = new Client($token);
    }

    /**
     * @param LoopInterface $loop
     * @param null|LoggerInterface $logger
     */
    public function onRegister(LoopInterface $loop, ?LoggerInterface $logger): void
    {
        $this->client->loop($loop);
        $this->client->logger($logger);

        parent::onRegister($loop, $logger);
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return UserInterface
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\ClientException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Throwable
     */
    public function auth(): UserInterface
    {
        if ($this->auth === null) {
            $data = $this->client->authUser();

            $this->auth = $this->getUser($data['id'], function() use ($data) {
                return new GitterUser($this, $data);
            });
        }

        return $this->auth;
    }

    /**
     * @param string $channelId
     * @return ChannelInterface
     * @throws \Throwable
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\ClientException
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function channel(string $channelId): ChannelInterface
    {
        return $this->getChannel($channelId, function () use ($channelId): ChannelInterface {
            $data = $this->client->rooms->join($channelId);

            return new GitterChannel($this, $data);
        });
    }

    /**
     * @param string $channelId
     * @return bool
     */
    public function has(string $channelId): bool
    {
        try {
            $this->channel($channelId);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @return \Traversable|ChannelInterface[]|GitterChannel[]
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\ClientException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Throwable
     */
    public function channels(): \Traversable
    {
        $empty    = true;
        $channels = $this->identities(ChannelInterface::class);

        foreach ($channels as $channel) {
            $empty = false;

            yield $channel;
        }

        if ($empty) {
            foreach ($this->client->rooms->all() as $room) {
                $channel = new GitterChannel($this, $room);

                $this->push(ChannelInterface::class, $channel->getId(), $channel);

                yield $channel;
            }
        }
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            'name' => $this->getName(),
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::SYSTEM_NAME;
    }
}
