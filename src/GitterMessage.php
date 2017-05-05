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
use Karma\Platform\Io\AbstractSystem;
use Karma\Platform\Io\SystemInterface;
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
        /** @var GitterSystem|AbstractSystem $system */
        $system   = $channel->getSystem();
        $mentions = $this->parseMentions($system, (array)($data['mentions'] ?? []));
        $user     = $this->getUserFromMessage($system, $data['fromUser']);
        $body     = $system->getTransformer()->parse($data['html'], $mentions);

        $system->info($data['html'] . "\n" . '   -> ' . "\n" . $body);

        parent::__construct($channel, $user, $data['id'], $body);

        $this->createdAt = Carbon::parse($data['sent']);
        $this->mentions  = $mentions;
    }

    /**
     * @param SystemInterface|GitterSystem $system
     * @param array $data
     * @return array
     */
    private function parseMentions(SystemInterface $system, array $data): array
    {
        $mentions = [];

        foreach ($data as $mention) {
            if (!isset($mention['userId'])) {
                continue;
            }

            $mentions[] = $system->getUser($mention['userId'], function () use ($system, $mention) {
                return new GitterUser($system, $mention);
            });
        }

        return $mentions;
    }

    /**
     * @param SystemInterface|GitterSystem $system
     * @param array $data
     * @return UserInterface
     */
    private function getUserFromMessage(SystemInterface $system, array $data): UserInterface
    {
        /** @var GitterUser $user */
        $user = $system->getUser($data['id'], function () use ($system, $data): UserInterface {
            return new GitterUser($system, $data);
        });

        $user->rename($data['username']);
        $user->setAvatar($data['avatarUrl'] ?? null);

        return $user;
    }
}
