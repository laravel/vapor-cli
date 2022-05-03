<?php

namespace Laravel\VaporCli;

use Illuminate\Support\Str;

class RewriteAssetUrls
{
    /**
     * Rewrite relative asset URLs in the given CSS string.
     *
     * @param  string  $css
     * @param  string  $baseUrl
     * @return string
     */
    public static function inCssString($css, $baseUrl)
    {
        return preg_replace_callback('/url\([\'"]?(?<url>[^)]+?)[\'"]?\)/', function ($matches) use ($baseUrl) {
            return Str::startsWith($matches[1], '/')
                        ? Str::replaceFirst('/', "{$baseUrl}/", $matches[0])
                        : $matches[0];
        }, $css);
    }
}
