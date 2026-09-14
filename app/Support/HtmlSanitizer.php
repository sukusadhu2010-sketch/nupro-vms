<?php

namespace App\Support;

/**
 * Minimal server-side HTML sanitizer for rich-text notes.
 * Strips <script>/<style> tags, inline event handlers (on*),
 * javascript: URIs and disallowed tags/attributes.
 */
class HtmlSanitizer
{
    public const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'span', 'div', 'blockquote',
    ];

    public const ALLOWED_ATTRS = ['style', 'href', 'class', 'target', 'rel'];

    public static function clean(?string $html): string
    {
        if (!$html) {
            return '';
        }

        // Remove script/style blocks entirely
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html);

        // Remove all tags not in the allowlist
        $html = preg_replace_callback('/<(\/?)((?:[a-zA-Z0-9]+))([^>]*)>/', function ($m) {
            $close = $m[1];
            $tag = strtolower($m[2]);
            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                return '';
            }
            if ($close) {
                return '</' . $tag . '>';
            }
            $attrs = $m[3] ?? '';
            // Strip inline event handlers and javascript: URIs
            $attrs = preg_replace('/\s*on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]*)/i', '', $attrs);
            $attrs = preg_replace('/(href|src)\s*=\s*("|\')\s*javascript:[^"\']*(\2)/i', '$1="#"', $attrs);
            // Keep only allowlisted attributes
            preg_match_all('/([a-zA-Z-]+)\s*=\s*("[^"]*"|\'[^\']*\')/', $attrs, $pairs, PREG_SET_ORDER);
            $kept = '';
            foreach ($pairs as $p) {
                $name = strtolower($p[1]);
                if (in_array($name, self::ALLOWED_ATTRS, true)) {
                    $kept .= ' ' . $name . '=' . $p[2];
                }
            }
            return '<' . $tag . $kept . '>';
        }, $html);

        return $html;
    }
}
