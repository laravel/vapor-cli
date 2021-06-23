<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Commands\Output\CommandResult;
use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class CommandAgainCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('command:again')
            ->addArgument('id', InputArgument::OPTIONAL, 'The command ID')
            ->setDescription('Re-execute a CLI command');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $command = $this->vapor->command(
            $this->argument('id') ?? $this->vapor->latestCommand()['id']
        );

        $environment = $this->vapor->environment(
            $command['environment']['project_id'],
            $command['environment']['id']
        );

        Helpers::line();
        Helpers::line('<fg=magenta>Vapor Command: </>php artisan '.$command['command']);
        Helpers::line('<fg=magenta>Vapor Command ID:</> '.$command['id']);
        Helpers::line('<fg=magenta>Vapor Environment:</> '.$environment['name']);
        Helpers::line('<fg=magenta>Vapor Project:</> '.$environment['project']['name']);

        if (! Helpers::confirm('Are you sure you want to run again this command?', true)) {
            return 0;
        }

        $command = $this->vapor->commandReRun($command['id']);

        (new CommandResult)->render($command);
    }
}
