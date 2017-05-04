<?php
/**
 * This file is part of Platform package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Karma\System\Gitter;

use Karma\Platform\Io\AbstractUser;
use Karma\Platform\Io\SystemInterface;

/**
 * Class GitterUser
 * @package Karma\System\Gitter
 */
class GitterUser extends AbstractUser
{
    /**
     * GitterUser constructor.
     * @param SystemInterface $system
     * @param array $data
     */
    public function __construct(SystemInterface $system, array $data)
    {
        $id    = $data['id'] ?? $data['userId'];
        $login = $data['username'] ?? $data['displayName'] ?? $data['screenName'];

        parent::__construct($system, $id, $login);

        $this->avatar = $data['avatarUrl'] ?? null;
    }

    /**
     * @param string $newName
     */
    public function rename(string $newName): void
    {
        $this->name = $newName;
    }

    /**
     * @param null|string $avatar
     */
    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }
}
