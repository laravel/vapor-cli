<?php

namespace Laravel\VaporCli;

use Symfony\Component\Console\Application as SymfonyConsoleApplication;
use Symfony\Component\Console\Input\InputOption;

class Application extends SymfonyConsoleApplication
{
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption('manifest', null, InputOption::VALUE_OPTIONAL, 'Path to your vapor manifest'));

        return $definition;
    }
}
