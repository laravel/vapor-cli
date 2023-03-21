<?php

namespace Laravel\VaporCli\Tests;

use Illuminate\Container\Container;
use Laravel\VaporCli\BuildProcess\ExecuteBuildCommands;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class ExecuteBuildCommandsTest extends TestCase
{
    protected $testManifest;

    protected function setUp(): void
    {
        parent::setUp();
        touch($this->testManifest = getcwd().'/test.vapor.yml');
        Container::getInstance()->offsetSet('manifest', $this->testManifest);
    }

    protected function tearDown(): void
    {
        @unlink(Container::getInstance()->offsetGet('manifest'));
        parent::tearDown();
    }

    public function test_unsupported_commands_are_removed()
    {
        file_put_contents($this->testManifest, Yaml::dump([
            'environments' => [
                'production' => [
                    'build' => [
                        'pa clear-compiled',
                        'php artisan migrate',
                        'php artisan clear-compiled',
                        'a optimize:clear',
                        'php artisan optimize',
                        'php artisan optimize:clear',
                    ],
                ],
            ],
        ]));

        $this->assertSame(
            ['php artisan migrate', 'php artisan optimize'],
            (new ExecuteBuildCommands('production'))->supportedCommands()
        );
    }
}
