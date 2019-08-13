<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class CommandLogCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('command:log')
            ->addArgument('id', InputArgument::OPTIONAL, 'The command ID')
            ->setDescription('Retrieve the log messages for an invoked command');
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

        Helpers::line('<info>Invocation Command:</info> '.$command['command']);
        Helpers::line('<info>Command Executed At:</info> '.$command['created_at'].' ('.Helpers::time_ago($command['created_at']).')');

        Helpers::line();

        isset($command['log']) && ! empty($command['log'])
                    ? Helpers::write(base64_decode($command['log']))
                    : Helpers::line('No log information is available for this invocation.');
    }
}
