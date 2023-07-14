<?php

namespace Laravel\VaporCli\Tests;

use Illuminate\Container\Container;
use Laravel\VaporCli\Docker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class DockerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        touch($testManifest = getcwd().'/test.vapor.yml');
        Container::getInstance()->offsetSet('manifest', $testManifest);
    }

    protected function tearDown(): void
    {
        @unlink(Container::getInstance()->offsetGet('manifest'));
        parent::tearDown();
    }

    public function test_build_command_no_build_args()
    {
        $command = Docker::buildCommand('my-project', 'production', [], [], [], [], []);
        $expectedCommand = 'docker build --pull --file=production.Dockerfile --tag=my-project:production   .';
        $this->assertEquals($expectedCommand, $command);
    }

    public function test_build_command_cli_build_args()
    {
        $cliBuildArgs = ['FOO=BAR', 'FIZZ=BUZZ'];
        $command = Docker::buildCommand('my-project', 'production', [], [], $cliBuildArgs, []);
        $expectedCommand = 'docker build --pull --file=production.Dockerfile --tag=my-project:production '.
            "--build-arg='FOO=BAR' --build-arg='FIZZ=BUZZ'  .";
        $this->assertEquals($expectedCommand, $command);
    }

    public function test_build_command_manifest_build_args()
    {
        $manifestBuildArgs = ['FOO' => 'BAR', 'FIZZ' => 'BUZZ'];
        $command = Docker::buildCommand('my-project', 'production', [], [], [], $manifestBuildArgs);
        $expectedCommand = 'docker build --pull --file=production.Dockerfile --tag=my-project:production '.
            "--build-arg='FOO=BAR' --build-arg='FIZZ=BUZZ'  .";
        $this->assertEquals($expectedCommand, $command);
    }

    public function test_build_command_cli_and_manifest_build_args()
    {
        $cliBuildArgs = ['BAR=FOO', 'FIZZ=BAZZ'];
        $manifestBuildArgs = ['FOO' => 'BAR', 'FIZZ' => 'BUZZ'];
        $command = Docker::buildCommand('my-project', 'production', [], [], $cliBuildArgs, $manifestBuildArgs);
        $expectedCommand = 'docker build --pull --file=production.Dockerfile --tag=my-project:production '.
            "--build-arg='FOO=BAR' --build-arg='FIZZ=BAZZ' --build-arg='BAR=FOO'  .";
        $this->assertEquals($expectedCommand, $command);
    }

    public function test_build_command_cli_docker_args()
    {
        $cliDockerArgs = ['FOO=BAR', 'FIZZ=BUZZ'];
        $command = Docker::buildCommand('my-project', 'production', $cliDockerArgs, [], [], []);
        $expectedCommand = 'docker build --pull --file=production.Dockerfile --tag=my-project:production  '.
            "--FOO='BAR' --FIZZ='BUZZ' .";
        $this->assertEquals($expectedCommand, $command);
    }

    public function test_build_command_manifest_docker_args()
    {
        $manifestDockerArgs = ['FOO' => 'BAR', 'FIZZ' => 'BUZZ'];
        $command = Docker::buildCommand('my-project', 'production', [], $manifestDockerArgs, [], []);
        $expectedCommand = 'docker build --pull --file=production.Dockerfile --tag=my-project:production  '.
            "--FOO='BAR' --FIZZ='BUZZ' .";
        $this->assertEquals($expectedCommand, $command);
    }

    public function test_build_command_cli_and_manifest_docker_args()
    {
        $cliDockerArgs = ['BAR=FOO', 'FIZZ=BAZZ'];
        $manifestDockerArgs = ['FOO' => 'BAR', 'FIZZ' => 'BUZZ'];
        $command = Docker::buildCommand('my-project', 'production', $cliDockerArgs, $manifestDockerArgs, [], []);
        $expectedCommand = 'docker build --pull --file=production.Dockerfile --tag=my-project:production  '.
            "--FOO='BAR' --FIZZ='BAZZ' --BAR='FOO' .";
        $this->assertEquals($expectedCommand, $command);
    }

    public function test_build_command_cli_docker_args_and_cli_build_args()
    {
        $cliDockerArgs = ['BAR=FOO', 'FIZZ=BAZZ'];
        $cliBuildArgs = ['BAR=FOO', 'FIZZ=BAZZ'];
        $command = Docker::buildCommand('my-project', 'production', $cliDockerArgs, [], $cliBuildArgs, []);
        $expectedCommand = 'docker build --pull --file=production.Dockerfile --tag=my-project:production '.
            "--build-arg='BAR=FOO' --build-arg='FIZZ=BAZZ' --BAR='FOO' --FIZZ='BAZZ' .";
        $this->assertEquals($expectedCommand, $command);
    }

    public function test_dockerfile_from_manifest()
    {
        file_put_contents(Container::getInstance()->offsetGet('manifest'), Yaml::dump([
            'id'           => 1,
            'name'         => 'Test',
            'environments' => [
                'production' => [
                    'runtime'    => 'docker',
                    'dockerfile' => 'docker/shared.Dockerfile',
                ],
            ],
        ]));
        $command = Docker::buildCommand('my-project', 'production', [], [], [], []);
        $expectedCommand = 'docker build --pull --file=docker/shared.Dockerfile --tag=my-project:production   .';
        $this->assertEquals($expectedCommand, $command);
    }
}
