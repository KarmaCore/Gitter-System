<?php
/**
 * This file is part of Platform package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Karma\System\Gitter;

use Karma\Platform\Io\AbstractMessage;
use Karma\Platform\Io\ChannelInterface;
use Karma\Platform\Io\UserInterface;

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
     * @param UserInterface $author
     * @param array $data
     */
    public function __construct(ChannelInterface $channel, UserInterface $author, array $data)
    {
        // TODO
    }
}
