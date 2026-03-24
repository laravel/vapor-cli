<?php

namespace Laravel\VaporCli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class Application extends SymfonyConsoleApplication
{
    /**
     * Adds a command object.
     *
     * Symfony Console v8 renamed add() to addCommand(). This override
     * ensures the vapor entrypoint works across Symfony 4–8.
     */
    public function add(Command $command): ?Command
    {
        if (method_exists(parent::class, 'add')) {
            return parent::add($command);
        }

        return $this->addCommand($command);
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $definition = parent::getDefaultInputDefinition();

        $definition->addOption(new InputOption('manifest', null, InputOption::VALUE_OPTIONAL, 'The path to your Vapor.yml manifest'));

        return $definition;
    }
}
