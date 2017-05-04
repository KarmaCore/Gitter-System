<?php
/**
 * This file is part of Platform package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Karma\System\Gitter;

use Karma\Platform\Io\UserInterface;
use Karma\Platform\Io\AbstractChannel;
use Karma\Platform\Io\SystemInterface;
use Karma\Platform\Io\MessageInterface;

/**
 * Class GitterChannel
 * @package Karma\System\Gitter
 *
 * @property-read GitterSystem|SystemInterface $system
 */
class GitterChannel extends AbstractChannel
{
    /**
     * GitterChannel constructor.
     * @param SystemInterface|GitterSystem $system
     * @param array $data
     */
    public function __construct(SystemInterface $system, array $data)
    {
        $name = $this->getChannelName($data);

        parent::__construct($system, $data['id'], $name);
    }

    /**
     * @param array $data
     * @return string
     */
    private function getChannelName(array $data): string
    {
        switch (true) {
            case isset($data['url']):
                return substr($data['url'], 1);

            case isset($data['uri']):
                return $data['uri'];

            default:
                return $data['name'] ?? 'undefined';
        }
    }

    /**
     * @param string|null $beforeId
     * @return \Traversable|MessageInterface[]
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\ClientException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Throwable
     */
    public function messages(string $beforeId = null): \Traversable
    {
        $messages = $this->system->getClient()->messages->allBeforeId($this->id, $beforeId);

        foreach ($messages as $message) {
            yield new GitterMessage($this, $message);
        }
    }

    /**
     * @param string $message
     * @return MessageInterface
     * @throws \Exception
     * @throws \Throwable
     */
    public function publish(string $message): MessageInterface
    {
        $response = $this->system->getClient()
            ->messages->create($this->getId(), $message);

        return new GitterMessage($this, $response);
    }

    /**
     * @param \Closure $then
     * @return void
     */
    public function subscribe(\Closure $then): void
    {
        // TODO: Implement subscribe() method.
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
