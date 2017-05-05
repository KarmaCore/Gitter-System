<?php
/**
 * This file is part of Platform package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Karma\System\Gitter;

use Karma\Platform\Ast\NodeList;
use Karma\Platform\Io\AbstractChannel;
use Karma\Platform\Io\SystemInterface;
use Karma\Platform\Io\MessageInterface;

/**
 * Class GitterChannel
 * @package Karma\System\Gitter
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
        /** @var GitterSystem $system */
        $system = $this->system;

        $messages = $system->getClient()->messages->allBeforeId($this->id, $beforeId);

        foreach ($messages as $message) {
            yield new GitterMessage($this, $message);
        }
    }

    /**
     * @param NodeList $nodes
     * @return MessageInterface
     * @throws \Exception
     * @throws \Throwable
     */
    public function publish(NodeList $nodes): MessageInterface
    {
        /** @var GitterSystem $system */
        $system = $this->system;

        $message = $system->getTransformer()->render($nodes);
        $response = $system->getClient()->messages->create($this->getId(), $message);

        return new GitterMessage($this, $response);
    }

    /**
     * @param \Closure $then
     * @throws \Throwable
     */
    public function subscribe(\Closure $then): void
    {
        /** @var GitterSystem $system */
        $system = $this->system;

        $observer = $system->getClient()->rooms->messages($this->getId());

        $observer->subscribe(function (array $data) use ($then) {
            $then(new GitterMessage($this, $data));
        });
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
