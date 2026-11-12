<?php

namespace App\Services;

class SafeProductHtml
{
    /** Keep only editorial formatting. No attributes, URLs, scripts or embedded content. */
    public function clean(string $html): string
    {
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>', LIBXML_HTML_NODEFDTD | LIBXML_NONET);
            $allowed = ['div', 'p', 'br', 'strong', 'b', 'em', 'i', 'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'span'];
            foreach (iterator_to_array($document->getElementsByTagName('*')) as $element) {
                if (in_array(strtolower($element->tagName), ['script', 'style', 'iframe', 'object', 'svg', 'math', 'template'], true)) {
                    $element->parentNode?->removeChild($element);

                    continue;
                }
                if (! in_array(strtolower($element->tagName), $allowed, true) && ! in_array($element->tagName, ['html', 'body'], true)) {
                    while ($element->firstChild) {
                        $element->parentNode?->insertBefore($element->firstChild, $element);
                    }
                    $element->parentNode?->removeChild($element);

                    continue;
                }
                while ($element->attributes->length) {
                    $element->removeAttributeNode($element->attributes->item(0));
                }
            }
            $body = $document->getElementsByTagName('body')->item(0);
            $result = '';
            foreach ($body?->childNodes ?? [] as $node) {
                $result .= $document->saveHTML($node);
            }

            return $result;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
