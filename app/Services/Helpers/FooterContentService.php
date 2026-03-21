<?php

namespace Pterodactyl\Services\Helpers;

use DOMDocument;
use DOMElement;
use DOMNode;

class FooterContentService
{
    private const ALLOWED_TAGS = [
        'a',
        'b',
        'br',
        'code',
        'em',
        'i',
        'small',
        'span',
        'strong',
        'u',
    ];

    public function render(?string $content): string
    {
        $content = $this->replaceTokens(trim((string) $content));

        if ($content === '') {
            return '';
        }

        if (!class_exists(DOMDocument::class)) {
            return $this->renderPlainText($content);
        }

        $previousValue = libxml_use_internal_errors(true);

        try {
            $document = new DOMDocument('1.0', 'UTF-8');
            $document->loadHTML(
                '<?xml encoding="utf-8" ?><div>' . $content . '</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
            );

            $container = $document->getElementsByTagName('div')->item(0);

            return $container instanceof DOMElement
                ? $this->sanitizeChildren($container)
                : $this->renderPlainText($content);
        } catch (\Throwable) {
            return $this->renderPlainText($content);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousValue);
        }
    }

    private function replaceTokens(string $content): string
    {
        $year = now()->format('Y');

        return str_replace(
            ['{{current_year}}', '{{year}}'],
            [$year, $year],
            $content,
        );
    }

    private function renderPlainText(string $content): string
    {
        return nl2br(e(html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function sanitizeChildren(DOMNode $node): string
    {
        $output = '';

        foreach ($node->childNodes as $child) {
            $output .= $this->sanitizeNode($child);
        }

        return $output;
    }

    private function sanitizeNode(DOMNode $node): string
    {
        if (in_array($node->nodeType, [XML_TEXT_NODE, XML_CDATA_SECTION_NODE], true)) {
            return e($node->nodeValue ?? '');
        }

        if ($node->nodeType !== XML_ELEMENT_NODE || !$node instanceof DOMElement) {
            return '';
        }

        $name = strtolower($node->tagName);

        if (!in_array($name, self::ALLOWED_TAGS, true)) {
            return $this->sanitizeChildren($node);
        }

        if ($name === 'br') {
            return '<br>';
        }

        if ($name === 'a') {
            return $this->sanitizeAnchor($node);
        }

        return sprintf('<%1$s>%2$s</%1$s>', $name, $this->sanitizeChildren($node));
    }

    private function sanitizeAnchor(DOMElement $element): string
    {
        $href = $this->sanitizeHref($element->getAttribute('href'));
        if ($href === null) {
            return $this->sanitizeChildren($element);
        }

        $attributes = ['href="' . e($href) . '"'];
        $target = strtolower(trim($element->getAttribute('target')));

        if (in_array($target, ['_blank', '_self'], true)) {
            $attributes[] = 'target="' . $target . '"';
        }

        $rel = $target === '_blank' ? 'noopener noreferrer nofollow' : 'nofollow';
        $attributes[] = 'rel="' . $rel . '"';

        return sprintf('<a %s>%s</a>', implode(' ', $attributes), $this->sanitizeChildren($element));
    }

    private function sanitizeHref(string $href): ?string
    {
        $href = trim($href);

        if ($href === '') {
            return null;
        }

        if (str_starts_with($href, '/') || str_starts_with($href, '#')) {
            return $href;
        }

        if (preg_match('/^(https?:|mailto:|tel:)/i', $href) === 1) {
            return $href;
        }

        return null;
    }
}
