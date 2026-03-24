<?php

namespace Laravel\VaporCli\Tests;

use Laravel\VaporCli\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

class ApplicationTest extends TestCase
{
    public function test_add_registers_command()
    {
        $app = new Application('Vapor Test', '0.0.0');

        $command = new Command('test:command');

        $result = $app->add($command);

        $this->assertSame($command, $result);
        $this->assertTrue($app->has('test:command'));
    }

}
