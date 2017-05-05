<?php
/**
 * This file is part of Platform package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Karma\System\Gitter\Message;

use Karma\Platform\Ast\CodeNode;
use Karma\Platform\Ast\NodeInterface;
use Karma\Platform\Ast\NodeList;
use Karma\Platform\Ast\TextNode;
use Karma\Platform\Ast\UserNode;
use Karma\Platform\Io\UserInterface;
use Karma\Platform\Ast\Transformer\ParserInterface;

/**
 * Class Parser
 * @package Karma\System\Gitter\Message
 */
class Parser implements ParserInterface
{
    /**
     * @var array|UserInterface[]
     */
    private $mentions = [];

    /**
     * @param string $html
     * @param array|UserInterface[] $mentions
     * @return NodeList
     */
    public function parse(string $html, array $mentions): NodeList
    {
        foreach ($mentions as $mention) {
            $this->mentions[$mention->getName()] = $mention;
        }

        $html = $this->removeUnusedTags($html);

        return new NodeList($this->transformXml($this->createRoot($html)));
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
     * @return \Generator
     */
    private function transformXml(\DOMNode $root): \Generator
    {
        if ($root->hasChildNodes()) {
            /** @var \DOMElement|\DOMText $child */
            foreach ($root->childNodes as $child) {
                yield from $this->transformChildren($child);
            }
        } else {
            yield new TextNode($root->textContent);
        }
    }

    /**
     * @param \DOMElement|\DOMText $child
     * @return \Generator
     */
    private function transformChildren($child): \Generator
    {
        if ($child instanceof \DOMText) {
            yield new TextNode($child->textContent);

        } else if ($child->tagName === 'span') {
            yield $this->parseUser($child);

        } else if ($child->tagName === 'code') {
            yield $this->parseCode($child);

        } else {
            yield from $this->transformXml($child);
        }
    }

    /**
     * @param \DOMElement $user
     * @return UserNode|TextNode|NodeInterface
     */
    private function parseUser(\DOMElement $user): NodeInterface
    {
        $body = $user->textContent;
        $name = $user->getAttribute('data-screen-name');

        if (!isset($this->mentions[$name])) {
            return new TextNode($body);
        }

        /** @var UserInterface $mention */
        $mention = $this->mentions[$name];

        return UserNode::fromUserInterface($body, $mention);
    }

    /**
     * @param \DOMElement $code
     * @return CodeNode
     */
    private function parseCode(\DOMElement $code): CodeNode
    {
        $body = htmlspecialchars($code->textContent);

        return new CodeNode($body, $code->getAttribute('class') ?: null);
    }
}
