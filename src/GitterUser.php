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
        $user = parent::__construct($system, $data['id'], $data['username']);
        $user->avatar = $data['avatarUrl'];
    }
}
