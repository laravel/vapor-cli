<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class TinkerCommand extends CommandCommand
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('tinker')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name')
            ->addOption('code', null, InputOption::VALUE_OPTIONAL, 'The code to execute with Tinker')
            ->setDescription('Execute a code with Tinker');
    }

    /**
     * Get the command to run.
     *
     * @return string
     */
    protected function getCommand()
    {
        $code = $this->option('code') ?? Helpers::ask('What code would you like to execute');

        return 'tinker --execute '.escapeshellarg($code);
    }
}
