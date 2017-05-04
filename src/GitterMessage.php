<?php
/**
 * This file is part of Platform package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Karma\System\Gitter;

use Carbon\Carbon;
use Karma\Platform\Io\UserInterface;
use Karma\Platform\Io\AbstractMessage;
use Karma\Platform\Io\ChannelInterface;

/**
 * Class GitterMessage
 * @package Karma\System\Gitter
 */
class GitterMessage extends AbstractMessage
{
    /**
     * 5 * 60 sec = 5 min
     */
    protected const MESSAGE_EDIT_TIMEOUT = 5 * 60;

    /**
     * GitterMessage constructor.
     * @param ChannelInterface $channel
     * @param array $data
     */
    public function __construct(ChannelInterface $channel, array $data)
    {
        $user = $this->getUserFromMessage($channel, $data);

        parent::__construct($channel, $user, $data['id'], $data['html']);

        $this->createdAt = Carbon::parse($data['sent']);
    }

    /**
     * @param ChannelInterface $channel
     * @param array $data
     * @return UserInterface
     */
    private function getUserFromMessage(ChannelInterface $channel, array $data): UserInterface
    {
        /** @var GitterSystem $system */
        $system = $this->getChannel()->getSystem();

        return $system->getUser($data['fromUser'], function () use ($system, $data): UserInterface {
            return new GitterUser($system, $data['fromUser']);
        });
    }
}
