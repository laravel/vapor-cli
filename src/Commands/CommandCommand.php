<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Commands\Output\CommandResult;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CommandCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('command')
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment name')
            ->addOption('command', null, InputOption::VALUE_OPTIONAL, 'The command that should be executed')
            ->setDescription('Execute a CLI command');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        // First we will initiate the invocation on the service backend. Invocations run
        // in the background to avoid racking up compute time on the server side so I
        // don't have to pay for all invocations. We will then poll their statuses.
        $command = $this->vapor->invoke(
            Manifest::id(),
            $environment = $this->argument('environment'),
            $this->getCommand()
        );

        (new CommandResult)->render($command);
    }

    /**
     * Get the command to run.
     *
     * @return string
     */
    protected function getCommand()
    {
        return $this->option('command') ?? Helpers::ask('What command would you like to execute');
    }
}
