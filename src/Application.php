<?php

namespace Laravel\VaporCli;

use Symfony\Component\Console\Application as SymfonyConsoleApplication;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class Application extends SymfonyConsoleApplication
{
    protected function getDefaultInputDefinition(): InputDefinition
    {
        $definition = parent::getDefaultInputDefinition();

        $definition->addOption(new InputOption('manifest', null, InputOption::VALUE_OPTIONAL, 'The path to your Vapor.yml manifest'));

        return $definition;
    }
}
