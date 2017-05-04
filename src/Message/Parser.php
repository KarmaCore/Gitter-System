<?php
/**
 * This file is part of Platform package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Karma\System\Gitter\Message;

use Karma\Platform\Io\UserInterface;
use Karma\Platform\Transformer\ParserInterface;

/**
 * Class Parser
 * @package Karma\System\Gitter\Message
 */
class Parser implements ParserInterface
{
    /**
     * Inline code template
     */
    private const CODE_INLINE = '<code>%s</code>';

    /**
     * Block code template
     */
    private const CODE_BLOCK  = '<code language="%s">%s</code>';

    /**
     * User without avatar
     */
    private const USER_SHORT  = '<user id="%s" name="%s">%s</user>';

    /**
     * User with avatar
     */
    private const USER_FULL   = '<user id="%s" name="%s" avatar="%s">%s</user>';

    /**
     * @var array|UserInterface[]
     */
    private $mentions = [];

    /**
     * @param string $html
     * @param array|UserInterface[] $mentions
     * @return string
     */
    public function parse(string $html, array $mentions): string
    {
        foreach ($mentions as $mention) {
            $this->mentions[$mention->getName()] = $mention;
        }

        $html = $this->removeUnusedTags($html);

        $html = $this->transformXml($this->createRoot($html));

        return $html;
    }

    /**
     * @param string $html
     * @return string
     */
    private function removeUnusedTags(string $html): string
    {
        return strip_tags($html, '<code><span>');
    }

    /**
     * @param string $html
     * @return \DOMNode
     */
    private function createRoot(string $html): \DOMNode
    {
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<html>' . $html . '</html>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        return $dom;
    }

    /**
     * @param \DOMNode $root
     * @return string
     */
    private function transformXml(\DOMNode $root): string
    {
        if ($root->hasChildNodes()) {
            $result = '';

            /** @var \DOMElement|\DOMText $child */
            foreach ($root->childNodes as $child) {
                $result .= $this->transformChildren($child);
            }

            return $result;
        }

        return $root->textContent;
    }

    /**
     * @param \DOMElement|\DOMText $child
     * @return string
     */
    private function transformChildren($child): string
    {
        if ($child instanceof \DOMText) {
            return $child->textContent;
        }

        if ($child->tagName === 'span') {
            return $this->parseUser($child);
        }

        if ($child->tagName === 'code') {
            return $this->parseCode($child);
        }

        return $this->transformXml($child);
    }

    /**
     * @param \DOMElement $user
     * @return string
     */
    private function parseUser(\DOMElement $user): string
    {
        $body = $user->textContent;
        $name = $user->getAttribute('data-screen-name');

        if (!isset($this->mentions[$name])) {
            return $body;
        }

        /** @var UserInterface $mention */
        $mention = $this->mentions[$name];

        return $mention->getAvatar()
            ? sprintf(self::USER_FULL, $mention->getId(), $mention->getName(), $mention->getAvatar(), $body)
            : sprintf(self::USER_SHORT, $mention->getId(), $mention->getName(), $body);
    }

    /**
     * @param \DOMElement $code
     * @return string
     */
    private function parseCode(\DOMElement $code): string
    {
        $body = $code->textContent;

        if ($class = $code->getAttribute('class')) {
            return sprintf(self::CODE_BLOCK, $class, htmlspecialchars($body));
        }

        return sprintf(self::CODE_INLINE, htmlspecialchars($body));
    }
}
