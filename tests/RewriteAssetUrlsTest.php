<?php

namespace Laravel\VaporCli\Tests;

use Laravel\VaporCli\RewriteAssetUrls;
use PHPUnit\Framework\TestCase;

class RewriteAssetUrlsTest extends TestCase
{
    /**
     * @dataProvider assetUrlDataProvider
     */
    public function test_asset_urls_are_rewritten($css, $expected)
    {
        $this->assertEquals($expected, RewriteAssetUrls::inCssString($css, 'http://example.com'));
    }

    public function assetUrlDataProvider()
    {
        return [
            ['something url("/foo/bar.jpg") something', 'something url("http://example.com/foo/bar.jpg") something'],
            ['something url(/foo/bar.jpg) something', 'something url(http://example.com/foo/bar.jpg) something'],
            ['something url(\'/foo/bar.jpg\') something', 'something url(\'http://example.com/foo/bar.jpg\') something'],

            // doesn't rewrite absolute URLs...
            ['something url("https://foo.com/foo/bar.jpg") something', 'something url("https://foo.com/foo/bar.jpg") something'],

            // doesn't rewrite data URLs...
            ['something url(\'data:image:foo\') something', 'something url(\'data:image:foo\') something'],

            // doesn't rewrite data URLs...
            ['something url(\'data:image:foo\') something', 'something url(\'data:image:foo\') something'],
        ];
    }
}
