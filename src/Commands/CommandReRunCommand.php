<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class CommandReRunCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('command:re-run')
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

        if (Helpers::confirm('Are you sure you want to re-run this command?', true)) {
            return $this->call('command', [
                'environment' => $environment['name'],
                '--command' => $command['command'],
            ]);
        }
    }
}
