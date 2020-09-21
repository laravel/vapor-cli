<?php

namespace Laravel\VaporCli\Tests;

use Laravel\VaporCli\AssetFiles;
use PHPUnit\Framework\TestCase;

class AssetFilesTest extends TestCase
{
    /**
     * @dataProvider assetFilesProvider
     */
    public function test_extracted_asset_files($manifestPublicFiles, $expectedAssetFiles)
    {
        $extractedAssetFiles = collect(
            AssetFiles::get(__DIR__.'/Fixtures/public', $manifestPublicFiles)
        )->map->getRelativePathname()->values()->all();

        $this->assertEquals($expectedAssetFiles, $extractedAssetFiles);
    }

    public function assetFilesProvider()
    {
        $default = collect([
            '.well-known/assetlinks.json',
            'css/app.css',
            'css/mobile.css',
            'images/header.jpg',
            'images/logo.jpg',
            'js/navigation.js',
            'root-level.css',
            'root-level.js',
        ]);

        $defaultWithout = function (...$files) use ($default) {
            foreach ($files as $file) {
                $default = $default->flip()->forget($file)->flip();
            }

            return $default->values()->sort()->all();
        };

        return [
            // Test default assets
            [[], $default->all()],
            [['css/something-not-there.css'], $default->all()],

            // Test ignore specific file
            [['custom.js'], $defaultWithout('custom.js')],

            // Test ignore specific file in subfolder
            [['css/app.css'], $defaultWithout('css/app.css')],

            // Test ignore specific file type by folder
            [['css/*.css'], $defaultWithout('css/app.css', 'css/mobile.css')],

            // Test ignore specific file type at root level
            [['*.css'], $defaultWithout('root-level.css')],

            // Test ignore all css files
            [['**/*.css', '*.css'], $defaultWithout('css/app.css', 'css/mobile.css', 'root-level.css')],
        ];
    }
}
