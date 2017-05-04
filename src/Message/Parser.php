<?php
/**
 * This file is part of Platform package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Karma\System\Gitter\Message;

use Karma\Platform\Transformer\ParserInterface;

/**
 * Class Parser
 * @package Karma\System\Gitter\Message
 */
class Parser implements ParserInterface
{
    /**
     * @param string $html
     * @return string
     */
    public function parse(string $html): string
    {
        return $html;
    }
}
