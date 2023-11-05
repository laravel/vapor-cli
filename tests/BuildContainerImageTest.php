<?php

namespace Laravel\VaporCli\Tests;

use Illuminate\Container\Container;
use Laravel\VaporCli\BuildProcess\BuildContainerImage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;

class BuildContainerImageTest extends TestCase
{
    protected $testManifest;

    protected $dockerFile;

    protected function setUp(): void
    {
        parent::setUp();
        touch($this->testManifest = getcwd().'/test.vapor.yml');
        touch($this->dockerFile = getcwd().'/production.Dockerfile');
        Container::getInstance()->offsetSet('manifest', $this->testManifest);
        Container::getInstance()->offsetSet('output', new BufferedOutput);
    }

    protected function tearDown(): void
    {
        @unlink(Container::getInstance()->offsetGet('manifest'));
        @unlink($this->dockerFile);
        parent::tearDown();
    }

    /**
     * @dataProvider runtimeProvider
     */
    public function test_cannot_build_with_x86_runtime_and_arm_base_image($runtime, $dockerFileContents, $buildArgs, $expectation)
    {
        file_put_contents($this->dockerFile, $dockerFileContents);

        $this->assertSame(
            $expectation,
            (new BuildContainerImage('production'))->validateDockerFile('production', $runtime, $buildArgs)
        );
    }

    public function test_docker_build_arguments_can_be_formatted_correctly()
    {
        file_put_contents(Container::getInstance()->offsetGet('manifest'), Yaml::dump([
            'id' => 1,
            'name' => 'Test',
            'environments' => [
                'production' => [
                    'runtime' => 'docker',
                ],
            ],
        ]));

        $buildArgs = (new BuildContainerImage('production', ['FOO=BAR', 'BAR=BAZ'], []))->formatBuildArguments();

        $this->assertSame(['__VAPOR_RUNTIME=docker', 'FOO=BAR', 'BAR=BAZ'], $buildArgs);
    }

    public function test_runtime_variable_cannot_be_overridden()
    {
        file_put_contents(Container::getInstance()->offsetGet('manifest'), Yaml::dump([
            'id' => 1,
            'name' => 'Test',
            'environments' => [
                'production' => [
                    'runtime' => 'docker',
                ],
            ],
        ]));

        $buildArgs = (new BuildContainerImage('production', [], ['__VAPOR_RUNTIME=foo']))->formatBuildArguments();

        $this->assertSame(['__VAPOR_RUNTIME=docker'], $buildArgs);
    }

    public function runtimeProvider()
    {
        return [
            [
                'docker',
                'FROM laravelphp/vapor:php82-arm',
                [],
                false,
            ],
            [
                'docker',
                'FROM laravelphp/vapor:php82',
                [],
                true,
            ],
            [
                'docker-arm',
                'FROM laravelphp/vapor:php82',
                [],
                false,
            ],
            [
                'docker-arm',
                'FROM laravelphp/vapor:php82-arm',
                [],
                true,
            ],
            [
                'docker-arm',
                'FROM custom/image',
                [],
                true,
            ],
            [
                'docker',
                'FROM custom/image',
                [],
                true,
            ],
            [
                'docker',
                'FROM custom/image'.PHP_EOL.'FROM laravelphp/vapor:php82',
                [],
                true,
            ],
            [
                'docker',
                'FROM custom/image'.PHP_EOL.'FROM laravelphp/vapor:php82-arm',
                [],
                false,
            ],
            [
                'docker-arm',
                'FROM custom/image'.PHP_EOL.'FROM laravelphp/vapor:php82-arm',
                [],
                true,
            ],
            [
                'docker-arm',
                'FROM custom/image'.PHP_EOL.'FROM laravelphp/vapor:php82',
                [],
                false,
            ],
            [
                'docker',
                'FROM custom/vapor:php82-arm',
                [],
                true,
            ],
            [
                'docker-arm',
                'FROM custom/vapor:php82',
                [],
                true,
            ],

            // End of line tests...
            [
                'docker',
                "\r\nFROM laravelphp/vapor:php82-arm\r\n",
                [],
                false,
            ],
            [
                'docker',
                "\nFROM laravelphp/vapor:php82-arm\n",
                [],
                false,
            ],
            [
                'docker',
                "\nFROM laravelphp/vapor:php82-arm\n",
                [],
                false,
            ],
            [
                'docker-arm',
                "\nFROM laravelphp/vapor:php82\n",
                [],
                false,
            ],

            // PHP versions tests...
            [
                'docker',
                'FROM laravelphp/vapor:php82',
                [],
                true,
            ],
            [
                'docker',
                'FROM laravelphp/vapor:php83',
                [],
                true,
            ],
            [
                'docker-arm',
                'FROM laravelphp/vapor:php82-arm',
                [],
                true,
            ],
            [
                'docker-arm',
                'FROM laravelphp/vapor:php82-arm',
                [],
                true,
            ],
            [
                'docker-arm',
                'FROM laravelphp/vapor:php83-arm',
                [],
                true,
            ],
            [
                'docker-arm',
                'FROM laravelphp/vapor:php84-arm',
                [],
                false,
            ],
            [
                'docker-arm',
                'FROM laravelphp/vapor:php84-arm',
                [],
                false,
            ],

            // Build arguments
            [
                'docker-arm',
                "ARG VERSION=php81\nFROM laravelphp/vapor:\${VERSION}-arm",
                [],
                false,
            ],
            [
                'docker',
                "ARG VERSION=php81\nFROM laravelphp/vapor:\${VERSION}",
                [],
                false,
            ],
            [
                'docker-arm',
                "ARG VERSION=php81\nFROM laravelphp/vapor:\${VERSION}",
                ['VERSION' => 'php82-arm'],
                true,
            ],
            [
                'docker',
                "ARG VERSION=php81\nFROM laravelphp/vapor:\${VERSION}",
                ['VERSION' => 'php82'],
                true,
            ],
            [
                'docker-arm',
                "ARG VERSION=php81\nFROM laravelphp/vapor:\${VERSION}",
                ['VERSION' => 'php82-arm'],
                true,
            ],
            [
                'docker-arm',
                "ARG VERSION=php81\nFROM laravelphp/vapor:\${VERSION}",
                ['VERSION' => 'php82'],
                false,
            ],
            [
                'docker',
                "ARG VERSION=php81\nFROM laravelphp/vapor:\${VERSION}",
                ['VERSION' => 'php82-arm'],
                false,
            ],
        ];
    }
}
