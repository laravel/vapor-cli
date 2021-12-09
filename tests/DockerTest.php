<?php

namespace Laravel\VaporCli\Tests;

use Laravel\VaporCli\Docker;
use PHPUnit\Framework\TestCase;

class DockerTest extends TestCase
{
    public function test_build_command_no_build_args()
    {
        $command = Docker::buildCommand('my-project', 'production', [], []);
        $expectedCommand = 'docker build --pull --file=production.Dockerfile --tag=my-project:production .';
        $this->assertEquals($expectedCommand, $command);
    }

    public function test_build_command_cli_build_args()
    {
        $cliBuildArgs = ['FOO=BAR', 'FIZZ=BUZZ'];
        $command = Docker::buildCommand('my-project', 'production', $cliBuildArgs, []);
        $expectedCommand = 'docker build --pull --file=production.Dockerfile --tag=my-project:production '.
            "--build-arg='FOO=BAR' --build-arg='FIZZ=BUZZ' .";
        $this->assertEquals($expectedCommand, $command);
    }

    public function test_build_command_manifest_build_args()
    {
        $manifestBuildArgs = ['FOO' => 'BAR', 'FIZZ' => 'BUZZ'];
        $command = Docker::buildCommand('my-project', 'production', [], $manifestBuildArgs);
        $expectedCommand = 'docker build --pull --file=production.Dockerfile --tag=my-project:production '.
            "--build-arg='FOO=BAR' --build-arg='FIZZ=BUZZ' .";
        $this->assertEquals($expectedCommand, $command);
    }

    public function test_build_command_cli_and_manifest_build_args()
    {
        $cliBuildArgs = ['BAR=FOO', 'FIZZ=BAZZ'];
        $manifestBuildArgs = ['FOO' => 'BAR', 'FIZZ' => 'BUZZ'];
        $command = Docker::buildCommand('my-project', 'production', $cliBuildArgs, $manifestBuildArgs);
        $expectedCommand = 'docker build --pull --file=production.Dockerfile --tag=my-project:production '.
            "--build-arg='FOO=BAR' --build-arg='FIZZ=BAZZ' --build-arg='BAR=FOO' .";
        $this->assertEquals($expectedCommand, $command);
    }
}
